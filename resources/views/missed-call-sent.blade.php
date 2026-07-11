<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>{{ $tenantName }} - SMS Enviado</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:system-ui,sans-serif; background:#f3f4f6; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .card { background:white; border-radius:16px; padding:40px 32px; max-width:400px; width:90%; text-align:center; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
        .card .check { font-size:48px; margin-bottom:16px; }
        .card h1 { font-size:20px; margin-bottom:8px; color:#111827; }
        .card p { font-size:14px; color:#6b7280; line-height:1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="check">✅</div>
        <h1>SMS enviado!</h1>
        <p>O cliente receberá um link para deixar uma mensagem. Quando o cliente responder, será notificado.</p>
    </div>
</body>
</html>
