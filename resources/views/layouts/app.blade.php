<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SchoolPlus')</title>
    <link rel="shortcut icon" href="{{asset('dist/img/logo.png')}}" type="image/x-icon">


                    <link rel="manifest" href="{{ asset('manifest.json') }}">

                    <meta name="theme-color" content="#007bff">

                    <meta name="mobile-web-app-capable" content="yes">

                    <meta name="apple-mobile-web-app-capable" content="yes">

                    <meta name="apple-mobile-web-app-status-bar-style" content="default">

                    <meta name="apple-mobile-web-app-title" content="SchoolPlus">


    <!-- Bootstrap CSS -->
    <link href="{{ asset('dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('dist/css/font-awesome.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/select2.min.css') }}">


    <link rel="stylesheet" href="{{ asset('dist/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('dist/css/page-loader.css') }}">
    <!-- Google Fonts (via CDN et non fichier local) -->
{{--    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">--}}
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-footer-fixed">
    @include('layouts.loader')
<style>
    /* ===== CONTAINER ===== */
    .pro-breadcrumb {
        background: linear-gradient(135deg, #0b1f33, #102c44);
        border-radius: 16px;
        padding: 22px 26px 18px;
        box-shadow: 0 14px 32px rgba(11, 31, 51, 0.35);
        margin-bottom: 32px;
        color: #fff;
    }

    /* ===== TITRE ===== */
    .pro-breadcrumb .breadcrumb-title {
        font-size: 1.4rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        margin-bottom: 14px;
        color: #ffffff;
    }

    /* ===== ZONE NIVEAUX ===== */
    .pro-breadcrumb .breadcrumb-wrapper {
        background: linear-gradient(135deg, #1c3f5e, #295f7d);
        border-radius: 12px;
        padding: 10px 16px;
        backdrop-filter: blur(6px);
    }

    /* ===== BREADCRUMB ===== */
    .pro-breadcrumb .breadcrumb {
        margin: 0;
        padding: 0;
        background: transparent;
        font-size: 0.9rem;
    }

    .pro-breadcrumb .breadcrumb-item + .breadcrumb-item::before {
        content: "❯";
        color: rgba(255,255,255,0.65);
        padding: 0 12px;
        font-size: 0.75rem;
    }


    /* ===== ACTIF ===== */
    .pro-breadcrumb .breadcrumb-item.active {
        color: #e9f2f8;
        font-weight: 600;
    }
    /* === HOVER PRO LINKS === */
    .pro-breadcrumb .breadcrumb-item a {
        position: relative;
        color: rgba(255,255,255,0.88);
        text-decoration: none;
        font-weight: 500;
        padding-bottom: 2px;
        transition: color 0.25s ease;
    }

    /* underline animé */
    .pro-breadcrumb .breadcrumb-item a::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -2px;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #6dd5fa, #ffffff);
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    /* hover */
    .pro-breadcrumb .breadcrumb-item a:hover {
        color: #ffffff;
        text-shadow: 0 0 8px rgba(109, 213, 250, 0.6);
    }

    .pro-breadcrumb .breadcrumb-item a:hover::after {
        width: 100%;
    }



</style>


<div class="wrapper">
    @if (session('success') || session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    showCloseButton: true,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#fff',
                    customClass: {
                        popup: 'shadow-lg rounded-4 colorful-toast',
                        title: 'fw-semibold text-dark',
                    },
                    didOpen: (toast) => {
                        // Décale le toast vers le bas
                        toast.parentElement.style.top = '30px';

                        // Permet au timer de s’arrêter quand on survole
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);

                        // ✅ Ferme bien le toast quand on clique sur la croix
                        const closeBtn = toast.querySelector('.swal2-close');
                        if (closeBtn) {
                            closeBtn.addEventListener('click', () => {
                                Swal.close();
                            });
                        }
                    },
                    willClose: () => {
                        // ✅ Forcer la fermeture propre après timer ou clic
                        document.querySelectorAll('.swal2-container').forEach(el => el.remove());
                    }
                });

                @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: @json(session('success')),
                });
                @endif

                @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: @json(session('error')),
                });
                @endif
            });
        </script>

        <style>
            /* 🎨 Style général du toast */
            .swal2-container.swal2-top-end > .swal2-popup.colorful-toast {
                margin-top: 30px !important;
                animation: slideDown 0.4s ease-out;
                border-left: 5px solid transparent;
                transition: all 0.3s ease-in-out;
            }

            /* ✅ Couleur dynamique selon le type */
            .swal2-popup.swal2-toast.swal2-icon-success.colorful-toast {
                border-left-color: #28a745; /* Vert */
                background: linear-gradient(145deg, #e9fbee, #ffffff);
            }

            .swal2-popup.swal2-toast.swal2-icon-error.colorful-toast {
                border-left-color: #dc3545; /* Rouge */
                background: linear-gradient(145deg, #fdeaea, #ffffff);
            }

            /* ✨ Animation fluide */
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endif




    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Content Wrapper -->
    <div class="content-wrapper" style="margin-top: 10px">
        <section class="content-header">
            {{-- <div class="container-fluid">
                <h1>@yield('page-title', 'Dashboard')</h1>
            </div> --}}
        </section>

        <section class="content">
            @yield('content')
        </section>
    </div>

    <!-- Footer -->
    @include('layouts.footer')

</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        // const sidebar = document.querySelector('.main-sidebar'); // ✅ BON conteneur
        const activeLink = document.querySelector('.nav-sidebar .nav-link.active');
        const sidebar = document.querySelector('.sidebar');

        if (!sidebar || !activeLink) return;

        setTimeout(() => {
            const linkRect = activeLink.getBoundingClientRect();
            const sidebarRect = sidebar.getBoundingClientRect();

            const offset = linkRect.top - sidebarRect.top - (sidebar.clientHeight / 2);

            sidebar.scrollTo({
                top: sidebar.scrollTop + offset,
                behavior: "smooth"
            });

        }, 400); // délai + long car AdminLTE ouvre les menus après
    });
</script>

<!-- jQuery (toujours en premier) -->
<script src="{{ asset('dist/js/jquery-3.6.0.min.js') }}"></script>

<!-- Bootstrap (bundle inclut Popper.js automatiquement) -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

<!-- AdminLTE -->
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>

<!-- DataTables (⚠️ ordre corrigé) -->
<script src="{{ asset('dist/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('dist/js/dataTables.bootstrap5.min.js') }}"></script>

<!-- SweetAlert2 -->
<script src="{{ asset('dist/js/sweetalert2@11.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('dist/js/select2.min.js') }}"></script>
<script>
window.addEventListener('pageshow', function (event) {

    if (event.persisted) {
        window.location.reload();
    }

});


    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/service-worker.js')
                .then(function (registration) {
                    console.log(
                        'SchoolPlus Service Worker enregistré :',
                        registration.scope
                    );
                })
                .catch(function (error) {
                    console.error(
                        'Erreur Service Worker SchoolPlus :',
                        error
                    );
                });
        });
    }
</script>
@stack('scripts')



</body>
</html>
