<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SP - Nouveau mot de passe</title>

    <link rel="shortcut icon"
          href="{{ asset('dist/img/logo.png') }}"
          type="image/x-icon">

    <link href="{{ asset('dist/css/bootstrap.min.css') }}"
          rel="stylesheet">

    <link rel="stylesheet"
          href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

<style> /* ========================================================= PAGE RESET PASSWORD Thème violet / indigo ========================================================== */ body { background-image: url("{{ asset('dist/img/4.jpg') }}"); background-size: cover; background-position: center; background-repeat: no-repeat; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; } /* ========================================================= CARTE ========================================================== */ .login-card { width: 100%; max-width: 420px; background: linear-gradient( 145deg, rgba(88, 65, 150, 0.88), rgba(43, 32, 80, 0.90) ); border-radius: 22px; padding: 2rem; box-shadow: 0 15px 45px rgba(0, 0, 0, 0.35); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, 0.25); } /* ========================================================= HEADER ========================================================== */ .login-header { text-align: center; margin-bottom: 2rem; } .login-header h3 { font-weight: bold; color: #ffffff; margin-top: 15px; } .login-header p { color: rgba(255, 255, 255, 0.75); } /* ========================================================= ICÔNE RESET ========================================================== */ .reset-icon { width: 75px; height: 75px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: linear-gradient( 135deg, #a78bfa, #7c3aed ); color: #ffffff; font-size: 30px; box-shadow: 0 8px 25px rgba(124, 58, 237, 0.45); } /* ========================================================= LABELS ========================================================== */ .form-label { color: #ffffff; font-weight: 500; } /* ========================================================= INPUTS ========================================================== */ .form-control { background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.20); color: #ffffff; border-radius: 8px; height: 45px; } .form-control::placeholder { color: rgba(255, 255, 255, 0.60); } .form-control:focus { background: rgba(255, 255, 255, 0.18); color: #ffffff; border-color: #a78bfa; box-shadow: 0 0 0 0.2rem rgba(167, 139, 250, 0.25); } /* ========================================================= INPUT GROUP ========================================================== */ .input-group-text { background: rgba(124, 58, 237, 0.45); border: 1px solid rgba(255, 255, 255, 0.15); color: #ffffff; } /* ========================================================= BOUTON RESET ========================================================== */ .btn-reset { background: linear-gradient( 135deg, #8b5cf6, #6d28d9 ); color: #ffffff; border: none; border-radius: 30px; padding: 11px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 5px 18px rgba(109, 40, 217, 0.45); } .btn-reset:hover { background: linear-gradient( 135deg, #7c3aed, #5b21b6 ); color: #ffffff; transform: translateY(-2px); box-shadow: 0 8px 22px rgba(109, 40, 217, 0.55); } /* ========================================================= LIEN RETOUR ========================================================== */ .back-login { text-align: center; margin-top: 20px; } .back-login a { color: #ddd6fe; text-decoration: none; font-weight: 500; transition: 0.2s; } .back-login a:hover { color: #ffffff; text-decoration: underline; } /* ========================================================= MESSAGE D'INFORMATION ========================================================== */ .reset-info { background: rgba(167, 139, 250, 0.15); border: 1px solid rgba(167, 139, 250, 0.30); border-radius: 10px; padding: 12px 15px; color: #ede9fe; font-size: 14px; margin-bottom: 20px; } /* ========================================================= ALERT ========================================================== */ .alert { border-radius: 10px; } /* ========================================================= MOBILE ========================================================== */ @media (max-width: 576px) { .login-card { margin: 15px; padding: 1.5rem; } .reset-icon { width: 65px; height: 65px; font-size: 25px; } } </style> 

</head>

<body>

<div class="login-card">

    <div class="login-header">

        <img
            src="{{ asset('dist/img/logo.png') }}"
            alt="SchoolPlus Logo"
            class="brand-image img-circle elevation-3"
            style="
                opacity: .9;
                width:80px;
                height:80px;
            "
        >

        <h3>Nouveau mot de passe</h3>

        <p>
            Définissez un nouveau mot de passe de 8 caractères minimum.
        </p>

    </div>


    {{-- Messages d'erreur --}}

    @if($errors->any())

        <div class="alert alert-danger">

            <i class="fas fa-exclamation-triangle"></i>

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- Message danger --}}

    @if(session('danger'))

        <div class="alert alert-danger">

            <i class="fas fa-exclamation-triangle"></i>

            {{ session('danger') }}

        </div>

    @endif


    <form method="POST"
          action="{{ route('password.update') }}">

        @csrf

        <input
            type="hidden"
            name="token"
            value="{{ $token }}"
        >


        {{-- Email --}}

        <div class="mb-3">

            <label
                for="email"
                class="form-label">

                Adresse Email

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="fas fa-envelope"></i>

                </span>

                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="{{ old('email', $email) }}"
                    required
                    autofocus
                >

            </div>

        </div>


        {{-- Nouveau mot de passe --}}

        <div class="mb-3">

            <label
                for="password"
                class="form-label">

                Nouveau mot de passe

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="fas fa-lock"></i>

                </span>

                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    required
                    minlength="8"
                >

            </div>

        </div>


        {{-- Confirmation --}}

        <div class="mb-4">

            <label
                for="password_confirmation"
                class="form-label">

                Confirmer le mot de passe

            </label>

            <div class="input-group">

                <span class="input-group-text">

                    <i class="fas fa-lock"></i>

                </span>

                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="form-control"
                    required
                    minlength="8"
                >

            </div>

        </div>


        <div class="d-grid">

           <button type="submit" class="btn btn-reset btn-block"> <i class="fas fa-lock"></i> Réinitialiser mon mot de passe </button>

        </div>

    </form>


    <div class="text-center mt-3">

       <div class="back-login"> <a href="{{ route('login') }}"> <i class="fas fa-arrow-left"></i> Retour à la connexion </a> </div>

    </div>

</div>

</body>
</html>
