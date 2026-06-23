<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>{{ __('email_settings.edit.preview_iframe_title') }}</title>
    <style>
        /* Reset for the iframe — kill default margins so the email
           layout renders edge-to-edge like it will in a real client. */
        html, body { margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #111827;
            background: #ffffff;
            line-height: 1.5;
        }
        img { max-width: 100%; height: auto; }
        a { color: inherit; }
        /* Banner shown at the top of the iframe so the admin knows it's
           not the live email. Only visible inside the preview. */
        .preview-banner {
            position: sticky;
            top: 0;
            z-index: 9999;
            background: #fef3c7;
            color: #78350f;
            text-align: center;
            font-size: 12px;
            padding: 6px 12px;
            border-bottom: 1px solid #fbbf24;
        }
    </style>
</head>
<body>
    <div class="preview-banner">
        ⚠ {{ __('email_settings.edit.preview_heading') }} — {{ __('email_settings.edit.preview_help') }}
    </div>
    {!! $html !!}
</body>
</html>
