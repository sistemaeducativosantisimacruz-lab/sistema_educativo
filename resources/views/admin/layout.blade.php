<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistema Educativo') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {font-family: 'Inter', system-ui, sans-serif;}
        .card {background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;}
        .btn-primary {background: #6366F1; color: white; font-weight: 600; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer;}
        .btn-primary:hover {background: #4F46E5;}
        .btn-secondary {background: #6B7280; color: white; font-weight: 600; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer;}
        .btn-secondary:hover {background: #4B5563;}
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto p-4">
        @include('layouts.navigation')
        <main class="mt-6">
                        @yield('content')
        </main>
    </div>
</body>
</html>
