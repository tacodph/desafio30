<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Desafio 30 Dias') }}</title>
        <meta name="description" content="Desafio da Virada: 30 dias, 5 objetivos. Alimentação, movimento, hidratação, sono e uma habilidade nova. A gente cresce junto.">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="pt_BR">
        <meta property="og:site_name" content="Desafio da Virada">
        <meta property="og:title" content="Desafio da Virada — 30 dias, 5 objetivos">
        <meta property="og:description" content="30 dias para sair da intenção e começar sua transformação. A gente cresce junto!">
        <meta property="og:url" content="{{ request()->url() }}">
        <meta property="og:image" content="{{ url('/images/desafio-da-virada-30-dias-share-v2.png') }}">
        <meta property="og:image:type" content="image/png">
        <meta property="og:image:width" content="1254">
        <meta property="og:image:height" content="1254">
        <meta property="og:image:alt" content="Emblema do Desafio da Virada de 30 dias">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Desafio da Virada — 30 dias, 5 objetivos">
        <meta name="twitter:description" content="30 dias para sair da intenção e começar sua transformação. A gente cresce junto!">
        <meta name="twitter:image" content="{{ url('/images/desafio-da-virada-30-dias-share-v2.png') }}">
        <link rel="icon" href="/images/logo-mark-30-virada.png?v=30-v2" type="image/png">
        <link rel="apple-touch-icon" href="/images/logo-mark-30-virada.png?v=30-v2">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;0,800;0,900;1,700;1,800;1,900&family=Barlow:ital,wght@0,400;0,500;0,600;0,700;1,400;1,700&family=Kaushan+Script&display=swap" rel="stylesheet">

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
