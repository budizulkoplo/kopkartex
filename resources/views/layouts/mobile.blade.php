<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0f6fcf">
    <title>KOPKARTEX</title>
    <meta name="description" content="Mobilekit HTML Mobile UI Kit">
    <meta name="keywords" content="bootstrap 4, mobile template, cordova, phonegap, mobile, html" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" sizes="32x32">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/icon/192x192.png') }}">
    <!-- <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ filemtime(public_path('assets/css/style.css')) }}">
    <style>
        :root {
            --mobile-primary: #0f6fcf;
            --mobile-primary-dark: #0b4f9a;
            --mobile-accent: #f4b942;
            --mobile-ink: #17212f;
            --mobile-muted: #6f7d8f;
            --mobile-line: #e4edf3;
            --mobile-bg: #f5f8fb;
        }

        body {
            background: var(--mobile-bg);
            color: var(--mobile-ink);
            padding-bottom: calc(82px + env(safe-area-inset-bottom));
        }

        .appHeader {
            min-height: 56px;
            border-bottom: 0;
            box-shadow: 0 8px 24px rgba(15, 79, 154, .14);
        }

        .appHeader.bg-warning,
        .appHeader.bg-primary,
        .appHeader.bg-success {
            background: linear-gradient(135deg, var(--mobile-primary-dark), var(--mobile-primary)) !important;
        }

        .appHeader .pageTitle {
            max-width: calc(100vw - 112px);
            color: #fff;
            font-size: .92rem;
            font-weight: 800;
            letter-spacing: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .appHeader .headerButton {
            color: rgba(255, 255, 255, .92) !important;
        }

        #appCapsule {
            min-height: 100vh;
            padding-bottom: calc(92px + env(safe-area-inset-bottom));
        }

        #loader-wrapper {
            backdrop-filter: blur(4px);
        }

        .mobile-page {
            padding: 16px;
            margin-top: 40px;
        }

        .mobile-card,
        #appCapsule .card {
            border: 1px solid var(--mobile-line) !important;
            border-radius: 12px !important;
            box-shadow: 0 8px 22px rgba(24, 45, 66, .06) !important;
        }

        #appCapsule .form-control {
            min-height: 44px;
            border-color: var(--mobile-line);
            border-radius: 10px;
            color: var(--mobile-ink);
            background-color: #fff;
        }

        #appCapsule .form-control:focus {
            border-color: rgba(15, 111, 207, .55);
            box-shadow: 0 0 0 .2rem rgba(15, 111, 207, .12);
        }

        #appCapsule .btn-primary,
        .btn-primary {
            background: var(--mobile-primary) !important;
            border-color: var(--mobile-primary) !important;
        }

        #appCapsule .btn-success,
        .btn-success {
            background: #0b4f9a !important;
            border-color: #0b4f9a !important;
        }

        .floating-install-button {
            background: var(--mobile-primary) !important;
            box-shadow: 0 12px 24px rgba(15, 111, 207, .28) !important;
        }

        @media (max-width: 390px) {
            .mobile-page {
                padding-left: 12px;
                padding-right: 12px;
            }

            .appHeader .pageTitle {
                font-size: .84rem;
            }
        }
    </style>
    <link rel="manifest" href="/manifest.json">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register("{{ url('service-worker.js') }}");
            });
        }
    </script>

</head>

<body>

    <!-- Loader Wrapper -->
    <div id="loader-wrapper" style="display: none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:9999; justify-content:center; align-items:center;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- tombol install nya bro-->
    <button id="installButton" style="display:none;" class="floating-install-button">
        <ion-icon name="download-outline"></ion-icon>
    </button>

    @yield('header')

    <!-- App Capsule -->
    <div id="appCapsule">
        @yield('content')
    </div>
    <!-- * App Capsule -->

    @include('layouts.bottomNav')
    @include('layouts.script')
    
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loader = document.getElementById('loader-wrapper');

        // Tampilkan loader saat klik <a>, <button>, atau <input type="submit">
        document.querySelectorAll('a, button[type=submit], input[type=submit]').forEach(el => {
            el.addEventListener('click', function (e) {
                const href = el.getAttribute('href') ?? '';
                if (el.tagName === 'A' && (href.startsWith('http') || href.startsWith('#') || href === 'javascript:;')) {
                    return;
                }
                if (loader) loader.style.display = 'flex';
            });
        });

        // Tambahkan juga untuk event submit form
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function () {
                if (loader) loader.style.display = 'flex';
            });
        });
    });

    // Sembunyikan loader saat kembali dari cache (misalnya back button)
    window.addEventListener('pageshow', function (event) {
        const loader = document.getElementById('loader-wrapper');
        if (loader) loader.style.display = 'none';
    });
</script>

<script>
    let deferredPrompt;
    const installButton = document.getElementById('installButton');

    window.addEventListener('beforeinstallprompt', (e) => {
        // Mencegah dialog default muncul
        e.preventDefault();
        deferredPrompt = e;

        // Tampilkan tombol install
        installButton.style.display = 'inline-block';

        installButton.addEventListener('click', () => {
            installButton.style.display = 'none';
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    deferredPrompt = null;
                });
            }
        });
    });

    // Jika sudah terinstall, sembunyikan tombol
    window.addEventListener('appinstalled', () => {
        installButton.style.display = 'none';
        console.log('App successfully installed');
    });
</script>


</body>

</html>
