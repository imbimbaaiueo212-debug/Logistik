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
                    keyframes: {
                        fadeInUp: {
                            '0%':   { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            '0%':   { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp .45s cubic-bezier(.16,1,.3,1) both',
                        'fade-in': 'fadeIn .3s ease-out both',
                    },
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        body { background: #EEF1F6; }

        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible {
            outline: 2px solid #E85D2A;
            outline-offset: 2px;
        }

        /* Transisi halus default untuk elemen interaktif */
        a, button, .hs-link, .hs-toggle, .hs-sub-link, .hs-sub-toggle,
        input, select, textarea, tr, .shadow, [class*="rounded-"] {
            transition: background-color .18s ease, color .18s ease,
                        border-color .18s ease, box-shadow .22s ease,
                        transform .18s ease, opacity .18s ease;
        }

        /* Efek hover ringan untuk card/tombol */
        .hover-lift:hover { transform: translateY(-2px); }

        /* ===== Top loading bar (mirip NProgress) ===== */
        #page-loader {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #E85D2A, #ff8a5c);
            box-shadow: 0 0 8px rgba(232,93,42,.6);
            z-index: 9999;
            transition: width .3s ease, opacity .3s ease;
            opacity: 0;
        }
        #page-loader.loading {
            opacity: 1;
            width: 70%;
        }
        #page-loader.done {
            width: 100%;
            opacity: 0;
            transition: width .2s ease, opacity .4s ease .1s;
        }

        /* Konten utama fade-in saat pertama render */
        #page-content {
            animation: fadeInUp .45s cubic-bezier(.16,1,.3,1) both;
        }

        /* Scrollbar halus (opsional, tetap ringan) */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(15,27,51,0.18);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover { background-color: rgba(15,27,51,0.32); }
    </style>

    @stack('styles')
</head>
<body class="font-poppins text-navy-950 antialiased">

    {{-- Loading bar navigasi --}}
    <div id="page-loader"></div>

    @include('partials.home-sidebar')

    {{-- lg:pl-64 = lebar sidebar, supaya konten tidak tertutup sidebar --}}
    <div class="lg:pl-64">
        <div id="page-content" class="max-w-screen-2xl mx-auto px-4 sm:px-6 md:px-8 py-6 pb-16">
            @yield('content')
        </div>
    </div>

    <script>
        // ===== Loading bar sederhana saat navigasi antar halaman =====
        (function () {
            const bar = document.getElementById('page-loader');

            function startLoader() {
                bar.classList.remove('done');
                bar.classList.add('loading');
            }
            function finishLoader() {
                bar.classList.remove('loading');
                bar.classList.add('done');
                setTimeout(() => {
                    bar.classList.remove('done');
                    bar.style.width = '0%';
                }, 500);
            }

            // Jalankan saat klik link internal (bukan #, bukan target _blank, bukan modifier klik)
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (!link) return;
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || link.target === '_blank') return;
                if (e.metaKey || e.ctrlKey || e.shiftKey || link.hasAttribute('download')) return;
                if (link.origin && link.origin !== window.location.origin) return;
                startLoader();
            });

            // Jalankan saat submit form biasa (GET/POST navigasi penuh)
            document.addEventListener('submit', function (e) {
                if (e.target.tagName === 'FORM') startLoader();
            });

            window.addEventListener('pageshow', finishLoader);
        })();
    </script>

    @stack('scripts')
</body>
</html>