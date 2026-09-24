<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'biMBA Logistik') - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { poppins: ['Poppins', 'sans-serif'] },
                    colors: {
                        navy: { 950: '#0F1B33', 900: '#162749', 800: '#1D3361', 700: '#28447F', 600: '#3A548A' },
                        rust: { 500: '#E85D2A', 600: '#D14E1F' },
                        canvas: '#EEF1F6',
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(15, 27, 51, 0.05), 0 10px 26px -14px rgba(15, 27, 51, 0.16)',
                    },
                }
            }
        }
    </script>

    <style>
        body { background: #EEF1F6; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible {
            outline: 2px solid #E85D2A;
            outline-offset: 2px;
        }
    </style>

    @stack('styles')
</head>
<body class="font-poppins text-navy-950 antialiased">

    @include('partials.home-sidebar')

    {{-- lg:pl-64 = lebar sidebar, supaya konten tidak tertutup sidebar --}}
    <div class="lg:pl-64">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 md:px-8 py-6 pb-16">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>