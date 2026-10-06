@csrf

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Élève</label>
        <select name="eleve_id" id="eleve_id" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            @foreach($inscriptions as $inscription)
                <option value="{{ $inscription->eleve->id }}"
                        data-classe="{{ $inscription->classe->nom }}"
                        data-classe_id="{{ $inscription->classe->id }}"
                        data-annee="{{ $inscription->annee->nom }}"
                        data-annee_id="{{ $inscription->annee->id }}"
                        data-type-inscription="{{ $inscription->type_inscription }}"
                    @selected(old('eleve_id', $paiement->eleve_id) == $inscription->eleve->id)>
                    {{ $inscription->eleve->nom }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Classe</label>
        <input type="text" id="classe_display" class="form-control"
               value="{{ optional($paiement->classe)->nom }}" readonly>
        <input type="hidden" name="classe_id" id="classe_id"
               value="{{ old('classe_id', $paiement->classe_id) }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Année scolaire</label>
        <input type="text" id="annee_display" class="form-control"
               value="{{ optional($paiement->annee)->nom ?? '-' }}" readonly>
        <input type="hidden" name="annee_id" id="annee_id"
               value="{{ old('annee_id', $paiement->annee_id) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Frais</label>
        <select name="frais_id" id="frais_id" class="form-select" required>
            <option value="">-- Sélectionner --</option>
            @foreach($frais as $f)
                <option value="{{ $f->id }}"
                    @selected(old('frais_id', $paiement->frais_id) == $f->id)>
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
               value="{{ old('montant_paye', $paiement->montant_paye) }}" required>
        <div id="montant_error" class="text-danger small mt-1 d-none"></div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Mode de paiement</label>
        <select name="mode_paiement" class="form-control" required>
            <option value="">-- Sélectionner --</option>
            <option value="Espèces" {{ old('mode_paiement', $paiement->mode_paiement) == 'Espèces' ? 'selected' : '' }}>Espèces</option>
            <option value="Mobile Money" {{ old('mode_paiement', $paiement->mode_paiement) == 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
            <option value="Chèque" {{ old('mode_paiement', $paiement->mode_paiement) == 'Chèque' ? 'selected' : '' }}>Chèque</option>
            <option value="Virement" {{ old('mode_paiement', $paiement->mode_paiement) == 'Virement' ? 'selected' : '' }}>Virement</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date" name="date_paiement" class="form-control"
               value="{{ old('date_paiement', \Carbon\Carbon::parse($paiement->date_paiement)->format('Y-m-d')) }}"
               required>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        let eleveSelect   = document.getElementById("eleve_id");
        let fraisSelect   = document.getElementById("frais_id");
        let inputMontant  = document.getElementById("montant_paye");
        let montantError  = document.getElementById("montant_error");
        let form          = inputMontant.closest("form");

        let montantTotal = 0;
        let dejaPaye     = 0;
        let restant      = 0;
        let libelleFrais = "";
        let paiementActuel = parseFloat(inputMontant.value) || 0; // ⚡ paiement en cours

        // ⚡ Remplissage automatique classe/année
        eleveSelect.addEventListener("change", function() {
            let option = this.options[this.selectedIndex];
            document.getElementById("classe_display").value = option.getAttribute("data-classe");
            document.getElementById("classe_id").value      = option.getAttribute("data-classe_id");
            document.getElementById("annee_display").value  = option.getAttribute("data-annee");
            document.getElementById("annee_id").value       = option.getAttribute("data-annee_id");
            updateFraisDisponibles(option.getAttribute("data-type-inscription"));
        });

        function updateFraisDisponibles(typeInscription) {
            const reinscrit = typeInscription === 'Réinscrit';
            Array.from(fraisSelect.options).forEach(option => {
                option.disabled = reinscrit && option.textContent.trim() === "Frais d'Inscription";
            });
            if (reinscrit && fraisSelect.selectedOptions[0]?.textContent.trim() === "Frais d'Inscription") {
                fraisSelect.value = '';
                document.getElementById("infos_frais").classList.add("d-none");
                inputMontant.value = '';
            }
        }

        // ⚡ Charger infos frais
        function loadFraisInfo() {
            let eleveId = eleveSelect.value;
            let fraisId = fraisSelect.value;

            if (eleveId && fraisId) {
                fetch(`/paiements/infos-frais/${eleveId}/${fraisId}`)
                    .then(res => res.json())
                    .then(data => {
                        montantTotal = data.total;
                        dejaPaye     = data.deja_paye;
                        libelleFrais = data.frais;

                        // ⚡ Calcul du montant restant autorisé pour modification
                        restant = montantTotal - (dejaPaye - paiementActuel);

                        let div = document.getElementById("infos_frais");
                        div.classList.remove("d-none");
                        div.innerHTML = `
                        <strong>${data.frais}</strong><br>
                        Montant total : ${data.total} FCFA<br>
                        Déjà payé : <span class="text-success">${data.deja_paye} FCFA</span><br>
                        Restant modifiable : <span class="text-danger">${restant} FCFA</span>
                    `;

                        montantError.classList.add("d-none");
                        montantError.innerText = "";
                        inputMontant.classList.remove("is-invalid");
                    });
            }
        }

        // ⚡ Vérification dynamique
        inputMontant.addEventListener("input", function() {
            let saisie = parseFloat(this.value);
            if (saisie > restant) {
                montantError.innerText = "❌ Le montant saisi dépasse le montant restant modifiable (" + restant + " FCFA).";
                montantError.classList.remove("d-none");
                inputMontant.classList.add("is-invalid");
            } else {
                montantError.classList.add("d-none");
                montantError.innerText = "";
                inputMontant.classList.remove("is-invalid");
            }
        });

        // ⚡ Empêcher la soumission si montant invalide
        form.addEventListener("submit", function(e) {
            let saisie = parseFloat(inputMontant.value);
            if (saisie > restant) {
                e.preventDefault();
                montantError.innerText = "❌ Impossible de valider : le montant dépasse le montant restant modifiable (" + restant + " FCFA).";
                montantError.classList.remove("d-none");
                inputMontant.classList.add("is-invalid");
            }
        });

        // ⚡ Charger les infos initiales
        const selectedOption = eleveSelect.options[eleveSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            updateFraisDisponibles(selectedOption.getAttribute("data-type-inscription"));
        }
        loadFraisInfo();
    });

</script>
