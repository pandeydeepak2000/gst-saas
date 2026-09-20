<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Auth' }} | GST-SaaS Cloud ERP</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full font-sans antialiased text-slate-900 bg-gradient-to-br from-slate-50 via-indigo-50/30 to-slate-100 flex items-center justify-center p-4 sm:p-8 relative overflow-x-hidden selection:bg-brand-500 selection:text-white">
    
    <!-- Ambient Background Glow Accents -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-brand-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-indigo-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="w-full max-w-5xl my-auto">
        {{ $slot }}
    </div>
</body>
</html>
