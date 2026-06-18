<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SP-Connexion</title>
    <link rel="shortcut icon" href="{{asset('dist/img/logo.png')}}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        body {
            background-image: url("{{ asset('dist/img/4.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h3 {
            font-weight: bold;
            color: #fff;
        }

        .login-header p {
            color: rgba(255,255,255,0.8);
        }

        .form-label {
            color: #fff;
            font-weight: 500;
        }

        .form-control {
            background: rgba(255,255,255,0.2);
            border: none;
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 8px rgba(78,115,223,0.6);
            background: rgba(255,255,255,0.3);
            color: #fff;
        }

        .btn-login {
            background: linear-gradient(135deg, #4e73df, #2e59d9);
            color: white;
            border-radius: 30px;
            padding: 10px;
            font-weight: 600;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(46,89,217,0.5);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #2e59d9, #224abe);
            transform: scale(1.05);
        }

        .extra-links {
            text-align: center;
            margin-top: 1rem;
        }

        .extra-links a {
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            cursor: pointer;
        }

        .extra-links a:hover {
            text-decoration: underline;
        }

        .input-group-text {
            background: rgba(255,255,255,0.2);
            border: none;
            color: #fff;
        }

        /* Modal style */
        .modal-content {
            border-radius: 15px;
            backdrop-filter: blur(10px);
            background: linear-gradient(185deg, #adc1f8, #66f1e4);
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">


        <img src="{{ asset('dist/img/logo.png') }}"
             alt="SchoolPlus Logo"
             class="brand-image img-circle elevation-3"
             style="opacity: .9; width:80px; height:80px;">


        <h3>Connexion</h3>
        <p class="text-muted small">Accédez à votre espace</p>
    </div>

    @if(session('danger'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            {{ session('danger') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('verifylogins') }}">
        @csrf
        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email / Username / Matricule</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="text" name="email" id="email" class="form-control"
                       value="{{ old('email', Auth::viaRemember() ? Auth::user()->email ?? Auth::user()->username ?? Auth::user()->matricule : '') }}"
                       required autofocus>
            </div>
        </div>

        <!-- Mot de passe -->
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
        </div>

        <!-- Remember me -->
        <div class="form-check mb-3">
            <input type="checkbox" name="remember" id="remember" class="form-check-input"
                {{ old('remember') || Auth::viaRemember() ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>

        <!-- Bouton -->
        <div class="d-grid">
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </div>
    </form>

    <div class="extra-links">
        <p class="mt-3 mb-1">
{{--            <a data-toggle="modal" data-target="#forgotPasswordModal">Mot de passe oublié ?</a>--}}
        </p>
        <p class="mb-0">Pas encore de compte ? Parlez à l'administration</p>
    </div>
</div>

<!-- 🔹 Modal Mot de passe oublié -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="forgotPasswordModalLabel"><i class="fas fa-unlock-alt"></i> Réinitialisation du mot de passe</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Entrez votre adresse email, vous recevrez un lien pour réinitialiser votre mot de passe.</p>
                <form method="POST" action="">
                    @csrf
                    <div class="mb-3" >
                        <label style="color: black !important;" for="resetEmail" class="form-label" >Adresse Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input style="color: black !important;" type="email" name="email" id="resetEmail" class="form-control" placeholder="exemple@mail.com" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-paper-plane"></i> Envoyer le lien
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="{{ asset('dist/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
