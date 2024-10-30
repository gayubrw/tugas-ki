<!-- resources/views/layouts/guest.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('APP_NAME', 'KI Assignment') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Custom animations */
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }
            .float-animation {
                animation: float 3s ease-in-out infinite;
            }

            /* Custom gradient background */
            .custom-gradient {
                background: linear-gradient(135deg, #EBF4FF 0%, #F9FAFB 50%, #F3E8FF 100%);
            }

            /* Smooth transitions */
            .smooth-transition {
                transition: all 0.3s ease-in-out;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased custom-gradient">
        {{ $slot }}
    </body>
</html>
