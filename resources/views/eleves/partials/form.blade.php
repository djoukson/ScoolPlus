<div class="mb-3">
    <label for="matricule" class="form-label">Matricule</label>
    <input type="text" name="matricule" class="form-control"
           value="{{ old('matricule', $eleve->matricule ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="nom" class="form-label">Nom</label>
    <input type="text" name="nom" class="form-control"
           value="{{ old('nom', $eleve->nom ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="prenom" class="form-label">Prénom</label>
    <input type="text" name="prenom" class="form-control"
           value="{{ old('prenom', $eleve->prenom ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="date_naissance" class="form-label">Date de naissance</label>
    <input type="date" name="date_naissance" class="form-control"
           value="{{ old('date_naissance', isset($eleve->date_naissance) ? $eleve->date_naissance->format('Y-m-d') : '') }}">
</div>

<div class="mb-3">
    <label for="sexe" class="form-label">Sexe</label>
    <select name="sexe" class="form-select">
        <option value="">-- Choisir --</option>
        <option value="M" {{ old('sexe', $eleve->sexe ?? '') == 'M' ? 'selected' : '' }}>Masculin</option>
        <option value="F" {{ old('sexe', $eleve->sexe ?? '') == 'F' ? 'selected' : '' }}>Féminin</option>
    </select>
</div>

<div class="mb-3">
    <label for="adresse" class="form-label">Adresse</label>
    <input type="text" name="adresse" class="form-control"
           value="{{ old('adresse', $eleve->adresse ?? '') }}">
</div>

<div class="mb-3">
    <label for="tuteur_nom" class="form-label">Nom du tuteur</label>
    <input type="text" name="tuteur_nom" class="form-control"
           value="{{ old('tuteur_nom', $eleve->tuteur_nom ?? '') }}">
</div>

<div class="mb-3">
    <label for="tuteur_tel" class="form-label">Téléphone du tuteur</label>
    <input type="text" name="tuteur_tel" class="form-control"
           value="{{ old('tuteur_tel', $eleve->tuteur_tel ?? '') }}">
</div>
