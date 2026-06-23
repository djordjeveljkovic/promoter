<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\MailSetting;
use Illuminate\Contracts\View\View as ViewContract;
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
     * Show the current email configuration and list of available templates,
     * along with the form to add a new template and the source code of the
     * currently active template.
     */
    public function index(): ViewResponse
    {
        // Read-only snapshot of the current mail configuration (read from .env at boot time).
        $config = [
            'mailer'       => config('mail.default'),
            'host'         => config('mail.mailers.smtp.host'),
            'port'         => config('mail.mailers.smtp.port'),
            'username'     => config('mail.mailers.smtp.username'),
            'scheme'       => config('mail.mailers.smtp.scheme'),
            'password_set' => !empty(config('mail.mailers.smtp.password')),
            'from_address' => config('mail.from.address'),
            'from_name'    => config('mail.from.name'),
            'app_url'      => config('app.url'),
            'app_env'      => config('app.env'),
        ];

        $templates   = EmailTemplate::orderByDesc('is_active')->latest()->get();
        $active      = EmailTemplate::active();

        $defaultViewExists = View::exists(self::DEFAULT_VIEW);
        $defaultViewPath   = resource_path('views/' . str_replace('.', '/', self::DEFAULT_VIEW) . '.blade.php');

        // We never load the entire Blade source into the listing page (it can be
        // very large). Instead, we expose just the file path and let the user
        // open the dedicated viewer when needed.
        $defaultViewSize = $defaultViewExists ? File::size($defaultViewPath) : 0;

        // Snapshot of the active template source. We resolve it here so the
        // view can render a "what's currently being sent" panel without
        // having to round-trip to /default-source.
        $activeSource = $active ? $this->resolveTemplateSource($active) : null;

        // DB-overridable mail settings (form on the page edits these).
        $mailSettings = MailSetting::current();

        return view('pages.admin.email_settings.index', compact(
            'config',
            'templates',
            'active',
            'defaultViewExists',
            'defaultViewSize',
            'activeSource',
            'mailSettings',
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
            // Allow blank = "do not change" semantics only on update via a
            // separate checkbox, so we always re-store what the user typed.
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

        $row->mailer        = $data['mailer'] ?? null;
        $row->host          = $data['host'] ?? null;
        $row->port          = $data['port'] ?? null;
        $row->username      = $data['username'] ?? null;
        $row->encryption    = $encryption;
        $row->timeout       = $data['timeout'] ?? null;
        $row->from_address  = $data['from_address'] ?? null;
        $row->from_name     = $data['from_name'] ?? null;
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
            ->route('admin.email-settings.index')
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
            'to.email'      => __('alert.email_settings_test_recipient_invalid'),
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
                ->route('admin.email-settings.index')
                ->with('success', __('alert.email_settings_test_sent', ['to' => $to]));
        } catch (\Throwable $e) {
            Log::error('[EmailSettings] Test email failed: ' . $e->getMessage());
            return redirect()
                ->route('admin.email-settings.index')
                ->withInput()
                ->withErrors(['test_email' => __('alert.email_settings_test_failed', [
                    'error' => $e->getMessage(),
                ])]);
        }
    }

    /**
     * Display the Blade source of a single template (or the default email
     * blade) in a dedicated editor page.
     */
    public function edit(Request $request, EmailTemplate $emailTemplate): ViewResponse
    {
        $source = $this->resolveTemplateSource($emailTemplate);

        return view('pages.admin.email_settings.edit', [
            'template'   => $emailTemplate,
            'source'     => $source,
            'isDefault'  => false,
            'defaultView'=> self::DEFAULT_VIEW,
        ]);
    }

    /**
     * Show the source code of the *default* Blade email file
     * (emails/customer/tickets.blade.php) so the admin can review it and
     * decide whether to import it as a new editable template.
     */
    public function viewDefaultSource(): ViewResponse
    {
        $defaultViewPath = resource_path('views/' . str_replace('.', '/', self::DEFAULT_VIEW) . '.blade.php');

        $source = [
            'view_name'     => self::DEFAULT_VIEW,
            'absolute_path' => $defaultViewPath,
            'exists'        => File::exists($defaultViewPath),
            'content'       => File::exists($defaultViewPath) ? File::get($defaultViewPath) : '',
            'size'          => File::exists($defaultViewPath) ? File::size($defaultViewPath) : 0,
        ];

        return view('pages.admin.email_settings.edit', [
            'template'   => null,
            'source'     => $source,
            'isDefault'  => true,
            'defaultView'=> self::DEFAULT_VIEW,
        ]);
    }

    /**
     * Resolve the Blade source content for a given template.
     *
     * - If the template references a Blade view via `view_name`, we read the
     *   file from disk.
     * - If the template has inline `html_content`, we use that as the source.
     * - Otherwise we return an empty string.
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
     * Store a new email template.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:email_templates,name',
            'subject'      => 'required|string|max:255',
            'description'  => 'nullable|string|max:500',
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
            'view_name.string'     => __('alert.email_template_view_string'),
        ]);

        if (empty($data['view_name']) && empty($data['html_content'])) {
            return back()
                ->withInput()
                ->withErrors(['view_name' => __('alert.email_template_needs_view_or_html')]);
        }

        $shouldActivate = !empty($data['is_active']);
        unset($data['is_active']);

        if ($shouldActivate) {
            EmailTemplate::query()->update(['is_active' => false]);
        }

        $template = EmailTemplate::create($data);

        if ($shouldActivate) {
            $template->update(['is_active' => true]);
        }

        return redirect()
            ->route('admin.email-settings.index')
            ->with('success', __('alert.email_template_created', ['name' => $template->name]));
    }

    /**
     * Update an existing template's metadata. Source content (blade/html) is
     * updated through `updateTemplateSource` so we can persist to disk for
     * Blade-backed templates.
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
            'view_name'    => 'nullable|string|max:255',
        ]);

        $emailTemplate->update($data);

        return redirect()
            ->route('admin.email-settings.index')
            ->with('success', __('alert.email_template_updated', ['name' => $emailTemplate->name]));
    }

    /**
     * Persist edits made in the Blade/HTML source editor to a dedicated view
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
     * Mark the given template as the active one.
     */
    public function activateTemplate(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->activate();

        return redirect()
            ->route('admin.email-settings.index')
            ->with('success', __('alert.email_template_activated', ['name' => $emailTemplate->name]));
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
            ->route('admin.email-settings.index')
            ->with('success', __('alert.email_template_deleted', ['name' => $name]));
    }

    /**
     * Import the current default Blade email view into a new template row.
     * The new template references the default view via `view_name` so the
     * admin can see and edit it; saving the editor copies it into a safe
     * generated location.
     */
    public function seedFromDefault(Request $request): RedirectResponse
    {
        $defaultViewPath = resource_path('views/' . str_replace('.', '/', self::DEFAULT_VIEW) . '.blade.php');

        if (!File::exists($defaultViewPath)) {
            return back()->with('error', __('alert.email_template_view_not_found', ['view' => self::DEFAULT_VIEW]));
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:email_templates,name',
            'subject'     => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'activate'    => 'sometimes|boolean',
        ]);

        $shouldActivate = $request->boolean('activate');

        $template = EmailTemplate::create([
            'name'         => $data['name'],
            'subject'      => $data['subject'],
            'description'  => $data['description'] ?? __('email_settings.imported_default_description'),
            'view_name'    => self::DEFAULT_VIEW,
            'html_content' => null,
            'is_active'    => false,
        ]);

        if ($shouldActivate) {
            $template->activate();
        }

        return redirect()
            ->route('admin.email-settings.templates.edit', $template)
            ->with('success', __('alert.email_template_imported', ['name' => $template->name]));
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
