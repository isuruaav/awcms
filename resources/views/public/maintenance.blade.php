<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance | {{ $settings->site_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-white antialiased">
    <main class="mx-auto flex min-h-screen max-w-3xl items-center px-6 py-16 text-center">
        <div class="w-full rounded-3xl border border-zinc-800 bg-zinc-900 p-10 shadow-2xl">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-400">Scheduled Maintenance</p>
            <h1 class="mt-4 text-3xl font-black">{{ $settings->site_name }}</h1>
            <p class="mx-auto mt-4 max-w-xl text-zinc-300">{{ $settings->maintenance_message ?: 'The website is temporarily unavailable while maintenance is being completed. Please check again shortly.' }}</p>
        </div>
    </main>
</body>
</html>
