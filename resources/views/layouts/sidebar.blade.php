<aside class="main-sidebar elevation-4" style="background: linear-gradient(180deg, #1f1f24 0%, #2a2a31 100%);">
    <!-- Logo -->


    @php
        use App\Models\Ecole;
        $ecole = Ecole::first(); // Récupère la première école
    @endphp

    @if(!empty($ecole->logo))

        <a href="{{ url('/') }}" class="brand-link text-center">
            <img src="{{ asset('storage/' . $ecole->logo) }}"
                 alt="SchoolPlus Logo"
                 class="brand-image img-circle elevation-3"
                 style="opacity:.9; width:40px; height:40px;">
            <span class="brand-text font-weight-bold ml-2">
            {{ $ecole->nom ?? 'SchoolPlus' }}
        </span>
        </a>
    @else
        <a href="{{ url('/') }}" class="brand-link text-center">
            <img src="{{ asset('dist/img/logo.png') }}"
                 alt="SchoolPlus Logo"
                 class="brand-image img-circle elevation-3"
                 style="opacity:.9; width:40px; height:40px;">
            <span class="brand-text ml-2">
            {{ $ecole->nom ?? 'SchoolPlus' }}
        </span>
        </a>
    @endif



    <!-- Sidebar -->
    <div class="sidebar">
        @php
            use Illuminate\Support\Facades\Auth;
            $user = Auth::user();
        @endphp

        <!-- Menu -->
        <nav class="mt-3">
            @include('layouts.menu')
        </nav>
    </div>
</aside>

@push('styles')
    <style>
        /* 🌙 Sidebar principal */
        .main-sidebar {

            color: #e5e7eb;
            border-right: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 4px 25px rgba(0,0,0,0.3);
        }

        /* Logo */
        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            transition: all 0.3s ease-in-out;
        }
        .brand-link:hover {
            background: rgba(255,255,255,0.1);
        }
        .brand-text {
            color: #fff !important;
            letter-spacing: 0.5px;
        }

        /* Panneau utilisateur */
        .sidebar .text-center img {
            border: 2px solid #00aaff;
            box-shadow: 0 0 8px rgba(0,170,255,0.3);
        }
        .sidebar .text-center h6 {
            color: #fff;
            font-weight: 600;
        }
        .sidebar .text-center small {
            font-size: 12px;
        }

        /* Liens principaux */
        .nav-sidebar .nav-item > .nav-link {
            border-radius: 8px;
            color: #d1d5db;
            margin: 3px 8px;
            padding: 9px 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .nav-sidebar .nav-item > .nav-link:hover {
            background-color: rgba(255,255,255,0.08);
            color: #fff;
            transform: translateX(4px);
        }
        .nav-sidebar .nav-item > .nav-link.active {
            background: linear-gradient(90deg, #007bff, #00aaff);
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(0,123,255,0.3);
            font-weight: 600;
        }

        /* Sous-menus */
        .nav-sidebar .nav-treeview {
            background: rgba(255,255,255,0.03);
            margin-left: 10px;
            border-left: 2px solid #00aaff;
            border-radius: 6px;
            padding: 4px 0;
            transition: all 0.3s ease;
        }
        .nav-sidebar .nav-treeview .nav-link {
            color: #cbd5e1;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
        }
        .nav-sidebar .nav-treeview .nav-link:hover {
            background-color: rgba(0,123,255,0.15);
            color: #fff;
            transform: translateX(5px);
        }
        .nav-sidebar .nav-treeview .nav-link.active {
            background-color: rgba(0,123,255,0.25);
            color: #fff;
        }

        /* Scrollbar custom */
        .nav-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .nav-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
        }
    </style>
@endpush
