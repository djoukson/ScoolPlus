<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de la licence</title>

    {{-- Bootstrap --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

</head>
<body class="bg-light">

<div class="modal show fade" id="licenseModal" tabindex="-1"
     style="display:block;background:rgba(0,0,0,0.65)">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    🔐 Activation de la licence
                </h5>
            </div>

            <div class="modal-body">

                <p class="mb-3">
                    Veuillez entrer la clé de licence fournie par votre administrateur.
                </p>

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('offline.license.activate') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label>Nom de l’école</label>
                        <input type="text"
                               name="school_name"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Clé licence</label>
                        <input type="text"
                               name="license_key"
                               class="form-control"
                               placeholder="XXXXX-XXXXX-XXXXX"
                               required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        ✅ Activer la licence
                    </button>

                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>
