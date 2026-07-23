<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('emails.magic_link_login.title') }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.6; color: #1a1a1a;">
    <div style="max-width: 480px; margin: 0 auto; padding: 40px 20px;">
        <h2 style="color: #2563eb;">{{ __('emails.magic_link_login.greeting', ['name' => $userName]) }}</h2>

        <p>{{ __('emails.magic_link_login.body') }}</p>

        <p>{{ __('emails.magic_link_login.cta_help') }}</p>

        <a href="{{ $magicLinkUrl }}" style="display: inline-block; margin: 24px 0; padding: 14px 32px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
            {{ __('emails.magic_link_login.cta') }}
        </a>

        <p style="font-size: 14px; color: #666;">{{ __('emails.magic_link_login.expiry') }}</p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">

        <p style="font-size: 13px; color: #999;">
            {{ __('emails.magic_link_login.unexpected') }}<br>
            {{ __('emails.magic_link_login.footer') }}
        </p>
    </div>
</body>
</html>
