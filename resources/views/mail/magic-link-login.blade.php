<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bem-vindo ao Lead Intake</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.6; color: #1a1a1a;">
    <div style="max-width: 480px; margin: 0 auto; padding: 40px 20px;">
        <h2 style="color: #2563eb;">👋 Bem-vindo, {{ $tenantName }}!</h2>

        <p>A sua conta no <strong>Lead Intake Assistant</strong> foi criada com sucesso.</p>

        <p>Clique no botão abaixo para iniciar sessão e definir a sua password:</p>

        <a href="{{ $magicLinkUrl }}" style="display: inline-block; margin: 24px 0; padding: 14px 32px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
            Iniciar Sessão
        </a>

        <p style="font-size: 14px; color: #666;">Este link é válido por 7 dias e só pode ser usado uma vez.</p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 32px 0;">

        <p style="font-size: 13px; color: #999;">
            Se não esperava receber este email, por favor ignore-o.<br>
            Lead Intake Assistant &mdash; Qualificação inteligente de leads para a sua empresa.
        </p>
    </div>
</body>
</html>
