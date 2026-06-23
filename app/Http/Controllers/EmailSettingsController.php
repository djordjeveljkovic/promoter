<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\MailSetting;
use App\Models\TicketOrder;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View as ViewResponse;

class EmailSettingsController extends Controller
{
    /**
     * Default path (relative to resources/views/) of the email blade that
     * Laravel uses when no active template is set.
     */
    public const DEFAULT_VIEW = 'emails.customer.tickets';

    /**
     * Directory in which dynamically-created email template views are stored.
     */
    protected string $generatedViewsDir = 'emails/customer/generated';

    /**
     * Main page — two tabs: "Configuration" (mail settings + test) and
     * "Templates" (list of templates with Edit / Add buttons).
     */
    public function index(Request $request): ViewResponse
    {
        $config = [
            'mailer'       => config('mail.default'),
            'host'         => config('mail.mailers.smtp.host'),
            'port'         => config('mail.mailers.smtp.port'),
            'username'     => config('mail.mailers.smtp.username'),
            'scheme'       => config('mail.mailers.smtp.scheme'),
            'password_set' => !empty(config('mail.mailers.smtp.password')),
            'from_address' => config('mail.from.address'),
            'from_name'    => config('mail.from.name'),
        ];

        $templates     = EmailTemplate::orderByDesc('is_active')->latest()->get();
        $active        = EmailTemplate::active();
        $mailSettings  = MailSetting::current();

        // Pick which tab to show: ?tab=config or ?tab=templates (default: config)
        $tab = $request->query('tab', 'config');
        if (!in_array($tab, ['config', 'templates'], true)) {
            $tab = 'config';
        }

        return view('pages.admin.email_settings.index', compact(
            'config', 'templates', 'active', 'mailSettings', 'tab',
        ))->with('defaultView', self::DEFAULT_VIEW);
    }

    /**
     * Update the persisted mail configuration (host, port, credentials,
     * from-envelope, mailer). Saves to the `mail_settings` singleton
     * table and applies the values to the runtime config so subsequent
     * `Mail::send()` calls in this same request already see them.
     */
    public function updateMailConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mailer'        => ['nullable', 'string', Rule::in([
                'smtp', 'sendmail', 'log', 'array', 'failover', 'roundrobin',
            ])],
            'host'          => 'nullable|string|max:255',
            'port'          => 'nullable|integer|min:1|max:65535',
            'username'      => 'nullable|string|max:255',
            'password'      => 'nullable|string|max:1024',
            'clear_password'=> 'sometimes|boolean',
            'encryption'    => 'nullable|string|in:tls,ssl,null,',
            'timeout'       => 'nullable|integer|min:1|max:600',
            'from_address'  => 'nullable|email|max:255',
            'from_name'     => 'nullable|string|max:255',
            'test_recipient'=> 'nullable|email|max:255',
        ], [
            'mailer.in'           => __('alert.email_settings_mailer_invalid'),
            'port.integer'        => __('alert.email_settings_port_invalid'),
            'encryption.in'       => __('alert.email_settings_encryption_invalid'),
            'from_address.email'  => __('alert.email_settings_from_address_invalid'),
            'test_recipient.email'=> __('alert.email_settings_test_recipient_invalid'),
        ]);

        $row = MailSetting::current();

        // Map encryption "null"/"" to null in the DB.
        $encryption = $data['encryption'] ?? null;
        if ($encryption === '' || $encryption === 'null') {
            $encryption = null;
        }

        $row->mailer         = $data['mailer'] ?? null;
        $row->host           = $data['host'] ?? null;
        $row->port           = $data['port'] ?? null;
        $row->username       = $data['username'] ?? null;
        $row->encryption     = $encryption;
        $row->timeout        = $data['timeout'] ?? null;
        $row->from_address   = $data['from_address'] ?? null;
        $row->from_name      = $data['from_name'] ?? null;
        $row->test_recipient = $data['test_recipient'] ?? null;

        // Password is a "leave alone" field by default — typing a new
        // value overwrites, leaving it blank keeps whatever was stored.
        // The "clear password" checkbox wipes it.
        if ($request->boolean('clear_password')) {
            $row->password_encrypted = null;
        } elseif (!empty($data['password'])) {
            $row->password = $data['password'];
        }

        $row->save();

        // Make sure the new config is active for the rest of this request
        // (e.g. for the "send test" follow-up).
        $row->applyToConfig();

        return redirect()
            ->route('admin.email-settings.index', ['tab' => 'config'])
            ->with('success', __('alert.email_settings_saved'));
    }

    /**
     * Send a one-off test email using the currently persisted mail
     * configuration. Recipient defaults to the configured test_recipient
     * or the From address; admin can override per-send.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to'      => 'nullable|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
        ], [
            'to.email' => __('alert.email_settings_test_recipient_invalid'),
        ]);

        $settings = MailSetting::current();

        $to = $data['to']
            ?? $settings->test_recipient
            ?? $settings->from_address
            ?? config('mail.from.address');

        if (empty($to)) {
            throw ValidationException::withMessages([
                'to' => __('alert.email_settings_test_recipient_required'),
            ]);
        }

        $subject = $data['subject'] ?? __('email_settings.test_email.default_subject');
        $body    = $data['message'] ?? __('email_settings.test_email.default_body', [
            'app_name' => config('app.name'),
            'host'     => config('mail.mailers.smtp.host') ?: '—',
            'port'     => config('mail.mailers.smtp.port') ?: '—',
            'mailer'   => config('mail.default'),
        ]);

        try {
            Mail::raw($body, function (Message $msg) use ($to, $subject) {
                $msg->to($to)->subject($subject);
            });

            return redirect()
                ->route('admin.email-settings.index', ['tab' => 'config'])
                ->with('success', __('alert.email_settings_test_sent', ['to' => $to]));
        } catch (\Throwable $e) {
            Log::error('[EmailSettings] Test email failed: ' . $e->getMessage());
            return redirect()
                ->route('admin.email-settings.index', ['tab' => 'config'])
                ->withInput()
                ->withErrors(['test_email' => __('alert.email_settings_test_failed', [
                    'error' => $e->getMessage(),
                ])]);
        }
    }

    /**
     * "Add new template" page — a simple form with name / subject /
     * description / source (view_name OR inline HTML) and a "make default"
     * checkbox.
     */
    public function createTemplate(): ViewResponse
    {
        return view('pages.admin.email_settings.create', [
            'defaultView' => self::DEFAULT_VIEW,
        ]);
    }

    /**
     * Persist a brand-new template.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:email_templates,name',
            'subject'      => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'source_type'  => ['required', Rule::in(['view', 'html'])],
            'view_name'    => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value !== null && $value !== '' && !View::exists($value)) {
                        $fail(__('alert.email_template_view_not_found', ['view' => $value]));
                    }
                },
            ],
            'html_content' => 'nullable|string',
            'is_active'    => 'sometimes|boolean',
        ], [
            'name.required'        => __('alert.email_template_name_required'),
            'name.unique'          => __('alert.email_template_name_unique'),
            'subject.required'     => __('alert.email_template_subject_required'),
            'source_type.required' => __('alert.email_template_source_type_required'),
            'source_type.in'       => __('alert.email_template_source_type_invalid'),
            'view_name.string'     => __('alert.email_template_view_string'),
        ]);

        if ($data['source_type'] === 'view') {
            if (empty($data['view_name'])) {
                return back()->withInput()->withErrors([
                    'view_name' => __('alert.email_template_view_required'),
                ]);
            }
            $viewName    = $data['view_name'];
            $htmlContent = null;
        } else {
            if (empty($data['html_content'])) {
                return back()->withInput()->withErrors([
                    'html_content' => __('alert.email_template_html_required'),
                ]);
            }
            $viewName    = null;
            $htmlContent = $data['html_content'];
        }

        $shouldActivate = !empty($data['is_active']);

        if ($shouldActivate) {
            EmailTemplate::query()->update(['is_active' => false]);
        }

        $template = EmailTemplate::create([
            'name'         => $data['name'],
            'subject'      => $data['subject'],
            'description'  => $data['description'] ?? null,
            'view_name'    => $viewName,
            'html_content' => $htmlContent,
            'is_active'    => false,
        ]);

        if ($shouldActivate) {
            $template->activate();
        }

        return redirect()
            ->route('admin.email-settings.templates.edit', $template)
            ->with('success', __('alert.email_template_created', ['name' => $template->name]));
    }

    /**
     * Edit template — split view with the code editor on the left and a
     * server-rendered preview (using sample data) on the right.
     */
    public function editTemplate(Request $request, EmailTemplate $emailTemplate): ViewResponse
    {
        $source = $this->resolveTemplateSource($emailTemplate);

        return view('pages.admin.email_settings.edit', [
            'template'    => $emailTemplate,
            'source'      => $source,
            'defaultView' => self::DEFAULT_VIEW,
        ]);
    }

    /**
     * Update template metadata (name, subject, description, default flag).
     * Source content (blade/html) is updated through `updateTemplateSource`
     * so we can persist to disk for Blade-backed templates.
     */
    public function updateTemplate(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $data = $request->validate([
            'name'         => [
                'required',
                'string',
                'max:255',
                Rule::unique('email_templates', 'name')->ignore($emailTemplate->id),
            ],
            'subject'      => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
            'is_active'    => 'sometimes|boolean',
        ]);

        $shouldActivate = !empty($data['is_active']);

        // Persist metadata first.
        $emailTemplate->update([
            'name'        => $data['name'],
            'subject'     => $data['subject'],
            'description' => $data['description'] ?? null,
        ]);

        // Handle the "use as default" checkbox — exclusive activation.
        if ($shouldActivate && !$emailTemplate->is_active) {
            $emailTemplate->activate();
        } elseif (!$shouldActivate && $emailTemplate->is_active) {
            $emailTemplate->update(['is_active' => false]);
        }

        return redirect()
            ->route('admin.email-settings.templates.edit', $emailTemplate)
            ->with('success', __('alert.email_template_updated', ['name' => $emailTemplate->name]));
    }

    /**
     * Persist edits made in the source code editor to a dedicated view
     * file on disk and update the template's `view_name` accordingly.
     */
    public function updateTemplateSource(Request $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $data = $request->validate([
            'content' => 'required|string',
        ]);

        // Decide which view file to write to:
        // - If the template already references a Blade view, we keep that
        //   path (overwriting it in-place) but only if it lives under our
        //   `emails/customer/generated/` directory. This protects the
        //   default view from accidental in-place edits via the admin UI.
        // - Otherwise we generate a new file based on the template id/name.
        $targetView = $emailTemplate->view_name;

        $generatedNamespace = $this->generatedViewsDir . '.';
        if (empty($targetView) || !str_starts_with($targetView, $generatedNamespace)) {
            $targetView = $this->generateViewNameFor($emailTemplate);
        }

        $absolutePath = resource_path('views/' . str_replace('.', '/', $targetView) . '.blade.php');
        $directory    = dirname($absolutePath);

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0775, true, true);
        }

        File::put($absolutePath, $data['content']);

        $emailTemplate->update([
            'view_name'    => $targetView,
            'html_content' => null,
        ]);

        return redirect()
            ->route('admin.email-settings.templates.edit', $emailTemplate)
            ->with('success', __('alert.email_template_source_saved', ['name' => $emailTemplate->name]));
    }

    /**
     * Server-rendered preview of the template using fake/sample data.
     * Used by the right-hand pane in the editor. Returns the rendered HTML
     * — NOT a full Blade view — so it can be dropped into an iframe via
     * `srcdoc` and the parent's styles won't leak in.
     */
    public function previewTemplate(Request $request, EmailTemplate $emailTemplate): ViewResponse
    {
        $sample = $this->buildSampleOrder();

        try {
            if ($emailTemplate->usesBladeView() && View::exists($emailTemplate->view_name)) {
                $html = View::make($emailTemplate->view_name, [
                    'order'          => $sample,
                    'currencySymbol' => 'RSD',
                    'template'       => $emailTemplate,
                ])->render();
            } elseif (!$emailTemplate->usesBladeView() && !empty($emailTemplate->html_content)) {
                $html = $this->renderInlineHtml($emailTemplate->html_content, $sample);
            } else {
                $html = View::make(self::DEFAULT_VIEW, [
                    'order'          => $sample,
                    'currencySymbol' => 'RSD',
                ])->render();
            }
        } catch (\Throwable $e) {
            $html = '<div style="padding:24px;font-family:sans-serif;color:#b91c1c;">'
                  . '<strong>Preview error:</strong> ' . htmlspecialchars($e->getMessage())
                  . '</div>';
        }

        // Bare HTML, no app layout — meant for the iframe srcdoc.
        return view('pages.admin.email_settings._preview_frame', [
            'html' => $html,
        ]);
    }

    /**
     * Create a duplicate of the given template (both metadata and source).
     * For Blade-backed templates we copy the underlying file to a new path,
     * so the duplicate can be edited independently of the original.
     */
    public function duplicateTemplate(EmailTemplate $emailTemplate): RedirectResponse
    {
        $copy = $emailTemplate->replicate(['is_active']);
        $copy->name = $this->uniqueName($emailTemplate->name);
        $copy->description = trim(($emailTemplate->description ?? '') . ' ' . __('email_settings.duplicate_suffix'));
        $copy->is_active = false;

        if ($emailTemplate->usesBladeView() && View::exists($emailTemplate->view_name)) {
            $sourcePath = resource_path('views/' . str_replace('.', '/', $emailTemplate->view_name) . '.blade.php');

            // Always materialize the duplicate under our generated directory
            // so the file path is unique per template and safe to overwrite.
            $copy->view_name = $this->generateViewNameFor($copy);
            $copy->html_content = null;

            $targetPath = resource_path('views/' . str_replace('.', '/', $copy->view_name) . '.blade.php');
            $targetDir  = dirname($targetPath);

            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0775, true, true);
            }

            File::copy($sourcePath, $targetPath);
        }

        $copy->save();

        return redirect()
            ->route('admin.email-settings.templates.edit', $copy)
            ->with('success', __('alert.email_template_duplicated', ['name' => $copy->name]));
    }

    /**
     * Remove an email template and its underlying Blade file (if any lives
     * in our generated directory).
     */
    public function destroyTemplate(EmailTemplate $emailTemplate): RedirectResponse
    {
        $name = $emailTemplate->name;

        // Clean up generated blade file if we created it.
        if ($emailTemplate->usesBladeView()
            && str_starts_with($emailTemplate->view_name, $this->generatedViewsDir . '.')) {
            $path = resource_path('views/' . str_replace('.', '/', $emailTemplate->view_name) . '.blade.php');
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $emailTemplate->delete();

        return redirect()
            ->route('admin.email-settings.index', ['tab' => 'templates'])
            ->with('success', __('alert.email_template_deleted', ['name' => $name]));
    }

    /**
     * Mark the given template as the active one.
     */
    public function activateTemplate(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->activate();

        return redirect()
            ->route('admin.email-settings.index', ['tab' => 'templates'])
            ->with('success', __('alert.email_template_activated', ['name' => $emailTemplate->name]));
    }

    /**
     * Resolve the Blade source content for a given template.
     *
     * @return array{view_name:?string, absolute_path:?string, exists:bool, content:string, size:int, source_kind:string}
     */
    protected function resolveTemplateSource(EmailTemplate $template): array
    {
        if ($template->usesBladeView()) {
            $path = resource_path('views/' . str_replace('.', '/', $template->view_name) . '.blade.php');
            return [
                'view_name'     => $template->view_name,
                'absolute_path' => $path,
                'exists'        => File::exists($path),
                'content'       => File::exists($path) ? File::get($path) : '',
                'size'          => File::exists($path) ? File::size($path) : 0,
                'source_kind'   => 'blade_view',
            ];
        }

        return [
            'view_name'     => null,
            'absolute_path' => null,
            'exists'        => true,
            'content'       => $template->html_content ?? '',
            'size'          => strlen((string) $template->html_content),
            'source_kind'   => 'inline_html',
        ];
    }

    /**
     * Build a fake TicketOrder with realistic items + tickets so the
     * preview pane always has something to render. Pulls real ticket
     * types from the DB and stubs ticket images so the QR-bearing rows
     * look the way they will in production.
     */
    protected function buildSampleOrder(): TicketOrder
    {
        $ticketTypes = TicketType::orderBy('id')->limit(3)->get();
        if ($ticketTypes->isEmpty()) {
            // No ticket types yet — fabricate a placeholder row so the
            // preview at least renders the text parts.
            $ticketTypes = collect([
                new TicketType([
                    'name'  => 'VIP',
                    'price' => 100,
                ]),
            ]);
        }

        // Build items from those types.
        $items = $ticketTypes->map(function (TicketType $tt, int $i) {
            return new \App\Models\TicketOrderItem([
                'ticket_type_id' => $tt->id,
                'quantity'       => $i === 0 ? 2 : 1,
                'price_at_order' => $tt->price ?? 0,
            ]);
        });

        // Build ticket rows — first item contributes multiple ticket codes,
        // the rest one each. We give them dummy image_path values so the
        // preview's @if($ticket->image_path) branches render the same way
        // they would in production.
        $tickets = collect();
        $counter = 0;
        foreach ($items as $itemIndex => $item) {
            $type = $ticketTypes[$itemIndex] ?? $ticketTypes->first();
            for ($n = 0; $n < $item->quantity; $n++) {
                $t = new \App\Models\Ticket([
                    'code'           => 'SAMPLE-' . strtoupper(substr(md5($counter . '|' . $type->id), 0, 8)),
                    'ticket_type_id' => $type->id,
                    'is_active'      => true,
                    // Empty image_path would skip the image in the default
                    // template, which is fine for preview. Admins can see
                    // the layout without generated PNGs cluttering the pane.
                    'image_path'     => null,
                ]);
                $t->setRelation('ticketType', $type);
                $tickets->push($t);
                $counter++;
            }
        }

        // Customer name — not stored on TicketOrder, so we fake it on a
        // dynamic property. The default template uses
        // `$order->customer_name` which would otherwise always be null.
        $order = new TicketOrder([
            'id'           => 999999,
            'order_number' => 'PREVIEW',
            'email'        => 'korisnik@primer.rs',
            'total'        => $ticketTypes->sum(fn ($t) => ($t->price ?? 0)),
            'paid'         => $ticketTypes->sum(fn ($t) => ($t->price ?? 0)),
            'job_status'   => 'completed',
        ]);
        $order->created_at = now();
        $order->customer_name = 'Marko';

        $order->setRelation('items', $items);
        $order->setRelation('tickets', $tickets);

        // Each item's ticketType relation is needed by the default template.
        foreach ($items as $i => $item) {
            $item->setRelation('ticketType', $ticketTypes[$i] ?? $ticketTypes->first());
        }

        return $order;
    }

    /**
     * Very small placeholder engine for templates defined as raw HTML.
     * Supports {{ $orderNumber }}, {{ $customerEmail }}, {{ $total }}.
     */
    protected function renderInlineHtml(string $html, TicketOrder $order): string
    {
        $total = '0.00';
        foreach ($order->items ?? [] as $item) {
            $total += (float) ($item->price_at_order ?? 0) * (int) ($item->quantity ?? 0);
        }

        $replacements = [
            '{{ $orderNumber }}'   => (string) ($order->order_number ?? $order->id),
            '{{ $customerEmail }}' => (string) ($order->email ?? ''),
            '{{ $total }}'         => number_format((float) $total, 2),
        ];

        return strtr($html, $replacements);
    }

    /**
     * Generate a unique Blade view name under our generated directory,
     * derived from the template's name and id.
     */
    protected function generateViewNameFor(EmailTemplate $template): string
    {
        $slug = \Illuminate\Support\Str::slug($template->name) ?: 'template';
        $slug = preg_replace('/[^a-z0-9_]+/', '_', strtolower($slug)) ?: 'template';

        // Use the template id once it's saved; for unsaved instances fall back
        // to a timestamp so the view name is still unique.
        $unique = $template->id ?? (int) (microtime(true) * 1000);

        return $this->generatedViewsDir . '.' . $slug . '_' . $unique;
    }

    /**
     * Ensure the generated name is unique by appending " (copy N)" as needed.
     */
    protected function uniqueName(string $base): string
    {
        $candidate = $base . ' (copy)';
        $counter   = 1;

        while (EmailTemplate::where('name', $candidate)->exists()) {
            $counter++;
            $candidate = $base . ' (copy ' . $counter . ')';
        }

        return $candidate;
    }
}
