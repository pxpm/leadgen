<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>LeadGen — Teste do Widget</title>
    <script>window.LEADGEN_TENANT = 'telhados-lisboa';</script>
    @vite('resources/js/widget/main.js')
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: system-ui, sans-serif; background: #f8fafc; min-height: 100vh; }
        .header { background: #1a56db; color: white; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; }
        .hero { text-align: center; padding: 80px 24px 40px; }
        .hero h2 { font-size: 28px; color: #111827; margin-bottom: 12px; }
        .hero p { color: #6b7280; font-size: 16px; margin-bottom: 24px; }
        .cta { display: inline-block; background: #1a56db; color: white; border: none; padding: 14px 32px; border-radius: 12px; font-size: 16px; cursor: pointer; text-decoration: none; }
        .cta:hover { background: #1e40af; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; padding: 0 24px 60px; max-width: 900px; margin: 0 auto; }
        .feature { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .feature h3 { font-size: 16px; margin-bottom: 8px; color: #111827; }
        .feature p { font-size: 14px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 Telhados Lisboa</h1>
        <span>📞 210 000 001</span>
    </div>

    <div class="hero">
        <h2>Precisa de ajuda com o seu telhado?</h2>
        <p>Responda a algumas perguntas e receba um orçamento rapidamente.</p>
        <button class="cta" data-leadgen-trigger>Pedir Orçamento Grátis</button>
    </div>

    <div class="features">
        <div class="feature">
            <h3>🕐 Resposta Imediata</h3>
            <p>Não precisa esperar por uma chamada de volta.</p>
        </div>
        <div class="feature">
            <h3>📸 Envie Fotos</h3>
            <p>Tire fotos do problema diretamente pelo telemóvel.</p>
        </div>
        <div class="feature">
            <h3>📋 Orçamento Rápido</h3>
            <p>Receba um orçamento baseado nas informações que partilhar.</p>
        </div>
    </div>
</body>
</html>
