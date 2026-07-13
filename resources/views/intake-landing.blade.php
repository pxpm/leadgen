<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>{{ $tenantName }} - Assistente</title>
    @vite('resources/js/widget/main.js')
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { height:100%; overflow:hidden; }
        body {
            font-family:system-ui,sans-serif;
            background: linear-gradient(155deg, #f8fafc 0%, {{ $primaryColor }}08 50%, #f1f5f9 100%);
            display:flex; align-items:center; justify-content:center;
            position:relative;
        }
        .lgw-shapes { position:fixed; inset:0; pointer-events:none; z-index:0; overflow:hidden; }
        .lgw-shapes .s1 {
            position:absolute; top:-100px; right:-80px;
            width:380px; height:380px; border-radius:60px;
            background: linear-gradient(135deg, {{ $primaryColor }}18, {{ $primaryColor }}0a);
            transform:rotate(25deg);
        }
        .lgw-shapes .s2 {
            position:absolute; bottom:-40px; left:-60px;
            width:0; height:0;
            border-left:200px solid transparent;
            border-right:200px solid transparent;
            border-bottom:350px solid {{ $primaryColor }}0d;
            transform:rotate(-10deg);
        }
        .lgw-shapes .s3 {
            position:absolute; top:8%; left:5%;
            width:180px; height:180px; border-radius:50%;
            background: radial-gradient(circle, {{ $primaryColor }}14 0%, transparent 70%);
            transform:translateY(-20px);
        }
        .lgw-shapes .s4 {
            position:absolute; bottom:15%; right:3%;
            width:120px; height:120px; border-radius:30px;
            background: {{ $primaryColor }}0c;
            transform:rotate(45deg);
        }
        .lgw-shapes .s5 {
            position:absolute; top:55%; left:12%;
            width:60px; height:60px; border-radius:50%;
            background: {{ $primaryColor }}10;
        }
        .lgw-shapes .s6 {
            position:absolute; top:45%; left:-20px;
            width:180px; height:3px; border-radius:2px;
            background: {{ $primaryColor }}15;
            transform:rotate(-8deg);
        }
        #widget-root { position:relative; z-index:1; width:100%; height:100%; max-width:480px; }
    </style>
</head>
<body>
    <div class="lgw-shapes">
        <div class="s1"></div>
        <div class="s2"></div>
        <div class="s3"></div>
        <div class="s4"></div>
        <div class="s5"></div>
        <div class="s6"></div>
    </div>
    <div id="widget-root"></div>
    <script>
        window.__LEADGEN_INTAKE__ = {
            token: '{{ $token }}',
            intent: '{{ $intent ?? '' }}',
            tenantName: '{{ $tenantName }}',
            tenantSlug: '{{ $tenantSlug }}',
        };
    </script>
</body>
</html>
