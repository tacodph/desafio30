<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Desafio da Virada</title>
    <link rel="icon" href="/images/logo-mark-30-virada.png" type="image/png">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#141210;color:#f5f0e8;font-family:system-ui,sans-serif;min-height:100vh;display:grid;place-items:center;padding:24px;background-image:radial-gradient(ellipse at top,rgba(246,168,33,.12),transparent 65%)}main{width:100%;max-width:620px;border:1px solid #393127;border-radius:28px;padding:clamp(24px,6vw,52px);background:#1b1815;box-shadow:0 24px 80px #0003}img{width:88px;height:88px;object-fit:contain}.brand{color:#f6a821;font-weight:800;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:20px 0}.code{display:inline-block;border:1px solid #5c4625;border-radius:999px;color:#f6a821;padding:6px 12px;font-size:13px;font-weight:700}h1{font-size:clamp(28px,6vw,42px);line-height:1.12;letter-spacing:-1px;margin:18px 0}p{color:#c2b9ae;line-height:1.7}nav{display:flex;flex-wrap:wrap;gap:12px;margin-top:28px}a{display:inline-block;padding:13px 20px;border-radius:12px;color:#f6a821;text-decoration:none;font-weight:700;border:1px solid #56442a}a.primary{background:#f6a821;color:#141210;border-color:#f6a821}a:hover{filter:brightness(1.12)}a:focus-visible{outline:3px solid #fff;outline-offset:4px}.note{font-size:13px;margin-top:28px;margin-bottom:0}
    </style>
</head>
<body>
    <main aria-labelledby="error-title">
        <img src="/images/logo-mark-30-virada.png" alt="Desafio da Virada — 30 dias">
        <div class="brand">A gente cresce junto</div>
        <span class="code">Erro {{ $status }}</span>
        <h1 id="error-title">{{ $title }}</h1>
        <p>{{ $description }}</p>
        <nav aria-label="Opções para continuar">
            <a class="primary" href="{{ $href }}">{{ $action }}</a>
            <a href="/suporte">Falar com o suporte</a>
        </nav>
        <p class="note">Se precisar de ajuda, informe ao suporte o código {{ $status }} e o que estava tentando fazer.</p>
    </main>
</body>
</html>
