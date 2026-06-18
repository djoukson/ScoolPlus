<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Erreur')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap / AdminLTE --}}
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <style>
        /* ===== Background animé avec gradient ===== */
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(-45deg, #6a11cb, #2575fc, #ff6a00, #ee0979);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Segoe UI', sans-serif;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* ===== Box d’erreur ===== */
        .error-box {
            max-width: 450px;
            width: 90%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease forwards;
        }

        @keyframes fadeInUp {
            0% {opacity: 0; transform: translateY(40px);}
            100% {opacity: 1; transform: translateY(0);}
        }

        .error-code {
            font-size: 100px;
            font-weight: 900;
            color: #dc3545;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .error-icon {
            font-size: 50px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        h4 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        p {
            color: #555;
            margin-bottom: 20px;
        }

        .btn-custom {
            background: linear-gradient(45deg, #ff6a00, #ee0979);
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<div class="error-box">
    {{-- Code d'erreur dynamique --}}
    <div class="error-code">@yield('code', '404')</div>

    {{-- Icone dynamique si besoin --}}
    <div class="error-icon">
        <i class="@yield('icon', 'fas fa-exclamation-triangle')"></i>
    </div>

    {{-- Titre et message dynamique --}}
    <h4>@yield('title', 'Oups !')</h4>
    <p>@yield('message', 'La page que vous cherchez n’existe pas ou a été déplacée.')</p>

    <a href="{{ url('/') }}" class="btn btn-custom">
        <i class="fas fa-home me-1"></i> Retour à l’accueil
    </a>
</div>

</body>
</html>
