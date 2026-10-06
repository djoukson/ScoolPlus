@csrf
<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Élève</label>
        <select name="eleve_id" id="eleve_id" class="form-select" required>
            <option value="" selected>-- Sélectionner --</option>
            @foreach($inscriptions as $inscription)
                <option value="{{ $inscription->eleve->id }}"
                        data-classe="{{ $inscription->classe->nom }}"
                        data-classe_id="{{ $inscription->classe->id }}"
                        data-annee="{{ $inscription->annee->nom }}"
                        data-annee_id="{{ $inscription->annee->id }}"
                        data-type-inscription="{{ $inscription->type_inscription }}"
                    @selected(old('eleve_id', $paiement->eleve_id ?? '') == $inscription->eleve->id)>
                    {{ $inscription->eleve->nom }} {{ $inscription->eleve->prenom }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Classe</label>
        <input type="text" id="classe_display" class="form-control"
               value="{{ $paiement->classe->nom ?? '' }}" readonly>
        <input type="hidden" name="classe_id" id="classe_id"
               value="{{ old('classe_id', $paiement->classe_id ?? '') }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Année scolaire</label>
        <input type="text" id="annee_display" class="form-control"
               value="{{ $paiement->annee->nom ?? '-' }}" readonly>
        <input type="hidden" name="annee_id" id="annee_id"
               value="{{ old('annee_id', $paiement->annee_id ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Frais</label>
        <select name="frais_id" id="frais_id" class="form-select" required>
            <option value="" selected>-- Sélectionner --</option>
            @foreach($frais as $f)
                <option value="{{ $f->id }}"
                    @selected(old('frais_id', $paiement->frais_id ?? '') == $f->id)>
                    {{ $f->libelle }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div id="infos_frais" class="alert alert-info d-none"></div>
@error('frais_id')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">Montant payé</label>
        <input type="number" id="montant_paye" name="montant_paye" class="form-control"
               value="{{ old('montant_paye', $paiement->montant_paye ?? '') }}" required>
        <div id="montant_error" class="text-danger small mt-1 d-none"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Mode de paiement</label>
        <select name="mode_paiement" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            @foreach(['Espèces','Mobile Money','Chèque','Virement'] as $mode)
                <option value="{{ $mode }}" {{ old('mode_paiement', $paiement->mode_paiement ?? '') == $mode ? 'selected' : '' }}>
                    {{ $mode }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date" name="date_paiement" class="form-control"
               value="{{ old('date_paiement', isset($paiement->date_paiement) ? $paiement->date_paiement->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
    </div>
</div>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let eleveSelect   = $('#eleve_id'); // ⚡ JQuery pour Select2
        let fraisSelect   = document.getElementById("frais_id");
        let inputMontant  = document.getElementById("montant_paye");
        let montantError  = document.getElementById("montant_error");
        let form          = inputMontant.closest("form");

        let montantTotal = 0;
        let dejaPaye     = 0;
        let restant      = 0;
        let libelleFrais = "";

        // Initialiser Select2
        eleveSelect.select2({
            placeholder: "-- Sélectionner un élève --",
            width: '100%',
            allowClear: true
        });

        // ⚡ Fonction pour remplir classe/année
        function updateClasseAnnee(option) {
            if(option) {
                document.getElementById("classe_display").value = option.dataset.classe;
                document.getElementById("classe_id").value      = option.dataset.classe_id;
                document.getElementById("annee_display").value  = option.dataset.annee;
                document.getElementById("annee_id").value       = option.dataset.annee_id;
                updateFraisDisponibles(option.dataset.typeInscription);
            } else {
                document.getElementById("classe_display").value = '';
                document.getElementById("classe_id").value      = '';
                document.getElementById("annee_display").value  = '-';
                document.getElementById("annee_id").value       = '';
                updateFraisDisponibles('Nouveau');
            }
        }

        function updateFraisDisponibles(typeInscription) {
            const reinscrit = typeInscription === 'Réinscrit';
            Array.from(fraisSelect.options).forEach(option => {
                const fraisInscription = option.textContent.trim() === "Frais d'Inscription";
                option.disabled = reinscrit && fraisInscription;
            });
            if (reinscrit && fraisSelect.selectedOptions[0]?.textContent.trim() === "Frais d'Inscription") {
                fraisSelect.value = '';
                document.getElementById("infos_frais").classList.add("d-none");
                inputMontant.value = '';
            }
        }

        // ⚡ Détecter changement sur Select2
        eleveSelect.on('select2:select select2:unselect', function(e){
            let option = e.params.data.element;
            updateClasseAnnee(option);
            loadFraisInfo(); // Mettre à jour les infos frais si nécessaire
        });

        // ⚡ Chargement infos frais
        function loadFraisInfo() {
            let eleveId = eleveSelect.val();
            let fraisId = fraisSelect.value;

            if(eleveId && fraisId) {
                fetch(`/paiements/infos-frais/${eleveId}/${fraisId}`)
                    .then(res => res.json())
                    .then(data => {
                        montantTotal = data.total;
                        dejaPaye     = data.deja_paye;
                        let reduction = data.reduction_bourse ?? 0;
                        restant      = data.reste;
                        libelleFrais = data.frais;

                        let div = document.getElementById("infos_frais");
                        div.classList.remove("d-none");
                        div.innerHTML = `
                                <strong>${data.frais}</strong><br>
                                ${data.exonere ? '<strong class="text-warning">Frais non dus : élève réinscrit</strong><br>' : ''}
                                Montant total : ${montantTotal} FCFA<br>
                                Réduction bourse : <span class="text-warning">${reduction} FCFA</span><br>
                                Déjà payé : <span class="text-success">${dejaPaye} FCFA</span><br>
                                Restant : <span class="text-danger">${restant} FCFA</span>
                            `;
                        montantError.classList.add("d-none");
                        montantError.innerText = "";
                        inputMontant.classList.remove("is-invalid");
                    });
            }
        }

        fraisSelect.addEventListener("change", loadFraisInfo);

        // Vérification dynamique montant
        inputMontant.addEventListener('input', function () {
            let saisie = parseFloat(this.value);
            if ((restant > 0 && saisie > restant) || (libelleFrais === "Frais d'Inscription" && saisie > restant)) {
                montantError.innerText = `❌ Le montant saisi dépasse le montant restant (${restant} FCFA).`;
                montantError.classList.remove("d-none");
                inputMontant.classList.add("is-invalid");
            } else {
                montantError.classList.add("d-none");
                montantError.innerText = "";
                inputMontant.classList.remove("is-invalid");
            }
        });

        // Empêcher soumission si montant invalide
        form.addEventListener("submit", function(e) {
            let saisie = parseFloat(inputMontant.value);
            if ((restant > 0 && saisie > restant) || (libelleFrais === "Frais d'Inscription" && saisie > restant)) {
                e.preventDefault();
                montantError.innerText = `❌ Impossible de valider : le montant dépasse le restant (${restant} FCFA).`;
                montantError.classList.remove("d-none");
                inputMontant.classList.add("is-invalid");
            }
        });

        // ⚡ Si édition → charger automatiquement
        let selectedOption = eleveSelect.find(':selected')[0];
        updateClasseAnnee(selectedOption);
        if(eleveSelect.val() && fraisSelect.value) loadFraisInfo();
    });
</script>
