<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class CustomerTicketsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public TicketOrder $order;
    public $tries = 3;
    public $timeout = 240;

    public function __construct(TicketOrder $order)
    {
        $this->order = $order->loadMissing([
            'items.ticketType',
            'tickets.ticketType',
        ]);
    }

    /**
     * Resolve the subject for this email. If an active EmailTemplate is set in
     * the database, its `subject` is used; otherwise we fall back to the
     * hard-coded default subject used historically by the application.
     */
    public function envelope(): Envelope
    {
        $active = EmailTemplate::active();

        $subject = $active && !empty($active->subject)
            ? $active->subject
            : 'Vaše ulaznice za REFEST 2025';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $active = EmailTemplate::active();

        // 1) Active template with a Blade view: use that view with the order data.
        if ($active && $active->usesBladeView() && View::exists($active->view_name)) {
            return new Content(
                view: $active->view_name,
                with: [
                    'order'           => $this->order,
                    'currencySymbol'  => 'RSD',
                    'template'        => $active,
                ],
            );
        }

        // 2) Active template with raw HTML: render the HTML body as a simple
        //    markdown view, with placeholders replaced.
        if ($active && !$active->usesBladeView() && !empty($active->html_content)) {
            $rendered = $this->renderInlineHtml($active->html_content);

            return new Content(
                htmlString: $rendered,
            );
        }

        // 3) Fallback to the default Blade view.
        return new Content(
            view: 'emails.customer.tickets',
            with: [
                'order'           => $this->order,
                'currencySymbol'  => 'RSD',
            ],
        );
    }

    /**
     * Very small placeholder engine for templates defined as raw HTML.
     * Supports the placeholders {{ $orderNumber }}, {{ $customerEmail }}
     * and {{ $total }} on top of whatever is passed via `with`.
     */
    protected function renderInlineHtml(string $html): string
    {
        $total = '0.00';
        foreach ($this->order->items ?? [] as $item) {
            $total += (float) ($item->price_at_order ?? 0) * (int) ($item->quantity ?? 0);
        }

        $replacements = [
            '{{ $orderNumber }}'  => (string) ($this->order->order_number ?? $this->order->id),
            '{{ $customerEmail }}' => (string) ($this->order->email ?? ''),
            '{{ $total }}'         => number_format((float) $total, 2),
        ];

        return strtr($html, $replacements);
    }

    public function attachments(): array
    {
        $attachments = [];
        Log::info("[CustomerTicketsMail] Processing attachments for Order ID: {$this->order->id}. Found {$this->order->tickets->count()} tickets.");

        $ticketTypeCounts = []; // Initialize an array to keep track of counts per ticket type
        foreach ($this->order->tickets as $ticket) {
            if ($ticket->image_path) {
                $ticketTypeId = $ticket->ticket_type_id;

                // Initialize count for this ticket type if it's the first time we see it
                if (!isset($ticketTypeCounts[$ticketTypeId])) {
                    $ticketTypeCounts[$ticketTypeId] = 0;
                }
                // Increment the count for this specific ticket type
                $ticketTypeCounts[$ticketTypeId]++;
                $currentTicketNumberForType = $ticketTypeCounts[$ticketTypeId];
                $pathOnPublicDisk = ltrim($ticket->image_path, '/');

                Log::info("[CustomerTicketsMail][Attachment] Checking ticket ID: {$ticket->id}, disk path: '{$pathOnPublicDisk}'");

                if (Storage::disk('public')->exists($pathOnPublicDisk)) {
                    Log::info("[CustomerTicketsMail][Attachment] File exists on 'public' disk: '{$pathOnPublicDisk}'. Attempting to attach.");
                    try {
                        $attachments[] = Attachment::fromStorageDisk('public', $pathOnPublicDisk)
                            ->as('Ulaznica_' . $ticket->ticketType->name . '_' . $currentTicketNumberForType . '.png')
                            ->withMime('image/png');
                        Log::info("[CustomerTicketsMail][Attachment] Successfully prepared attachment for ticket ID {$ticket->id}");
                    } catch (\Exception $e) {
                        Log::error("[CustomerTicketsMail][Attachment] Error creating attachment for ticket ID {$ticket->id} from 'public' disk path '{$pathOnPublicDisk}': " . $e->getMessage());
                    }
                } else {
                    Log::warning("[CustomerTicketsMail][Attachment] File NOT FOUND on 'public' disk for attachment: '{$pathOnPublicDisk}' (Ticket ID: {$ticket->id})");
                }
            } else {
                Log::warning("[CustomerTicketsMail][Attachment] Ticket ID {$ticket->id} (Order ID {$this->order->id}) is missing image_path, cannot attach.");
            }
        }
        Log::info("[CustomerTicketsMail][Attachment] Total attachments prepared for Order ID {$this->order->id}: " . count($attachments));
        return $attachments;
    }
}
