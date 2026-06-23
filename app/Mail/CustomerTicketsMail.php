<?php

namespace App\Mail;

use App\Models\TicketOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\Reverb\Loggers\Log;

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

    public function envelope(): Envelope
    {
        $eventNameForSubject = 'REFEST 2025';
        return new Envelope(
            subject: 'Vaše ulaznice za ' . $eventNameForSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.tickets',
            with: [
                'order'           => $this->order,
                'currencySymbol'  => 'RSD',
            ],
        );
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
