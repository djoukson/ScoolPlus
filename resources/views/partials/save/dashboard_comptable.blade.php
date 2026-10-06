<div class="row g-3">
    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card shadow-sm border-0 rounded-1 stat-box bg-success text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $totalEleves }}</h4>
                            <p class="mb-0 small">Élèves inscrits</p>
                        </div>
                        <i class="fas fa-user-graduate fa-2x opacity-75"></i>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-2 text-end">
                    </div>
                </div>

            </div>

            <!-- Filles -->
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card shadow-sm border-0 rounded-1 stat-box bg-danger text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $filles }}</h4>
                            <p class="mb-0 small">Filles</p>
                        </div>
                        <i class="fas fa-female fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- Garçons -->
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card shadow-sm border-0 rounded-1 stat-box bg-info text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ $garcons }}</h4>
                            <p class="mb-0 small">Garçons</p>
                        </div>
                        <i class="fas fa-male fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- Paiements de frais -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card shadow-sm border-0 rounded-1 stat-box bg-success text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <h4 class="fw-bold mb-1">Frais</h4>
                            <p class="mb-0 small">Gestion des paiements Scolaires</p>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-2 text-end">
                        <a href="{{ route('paiements.index') }}" class="text-white fw-bold small">
                            Gérer <span class="fas fa-arrow-circle-right ms-1"></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Services et souscriptions -->
            <div class="col-lg-6 col-md-6 col-sm-12">
                <div class="card shadow-sm border-0 rounded-1 stat-box bg-primary text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3">
                        <div>
                            <h4 class="fw-bold mb-1">Services</h4>
                            <p class="mb-0 small">Souscriptions et paiements des services</p>
                        </div>
                        <i class="fas fa-concierge-bell fa-2x opacity-75"></i>
                    </div>
                    <div class="card-footer bg-transparent border-0 p-2 text-end">
                        <a href="{{ route('services.paiementsouscriptions') }}" class="text-white fw-bold small">
                            Voir plus <span class="fas fa-arrow-circle-right ms-1"></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actions rapides spécifiques au comptable -->
            <div class="card shadow-sm border-0 rounded-3 mb-3 overflow-hidden">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-bolt me-2 text-warning"></i>Actions rapides
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">

                        <div class="col-4">
                            <a href="{{ route('frais.index') }}" class="quick-action bg-success-subtle text-success shadow-sm">
                                <i class="fas fa-list fa-lg mb-2"></i>
                                <span>Type de Frais scolaires</span>
                            </a>
                        </div>

                        <div class="col-4">
                            <a href="{{ route('montants_frais.index') }}" class="quick-action bg-info-subtle text-info shadow-sm">
                                <i class="fas fa-coins fa-lg mb-2"></i>
                                <span>Montants scolaires</span>
                            </a>
                        </div>

                        <div class="col-4">
                            <a href="{{ route('paiements.index') }}" class="quick-action bg-warning-subtle text-warning shadow-sm">
                                <i class="fas fa-money-check-alt fa-lg mb-2"></i>
                                <span>Paiements des frais scolaires</span>
                            </a>
                        </div>

                        <div class="col-4">
                            <a href="{{ route('services.souscriptions') }}" class="quick-action bg-primary-subtle text-primary shadow-sm">
                                <i class="fas fa-edit fa-lg mb-2"></i>
                                <span>Souscriptions aux services</span>
                            </a>
                        </div>

                        <div class="col-4">
                            <a href="{{ route('services.paiementsouscriptions') }}" class="quick-action bg-danger-subtle text-danger shadow-sm">
                                <i class="fas fa-chart-line fa-lg mb-2"></i>
                                <span>État des souscriptions</span>
                            </a>
                        </div>

                        <div class="col-4">
                            <a href="{{ route('userlogs', Auth::id()) }}" class="quick-action bg-secondary-subtle text-secondary shadow-sm">
                                <i class="fas fa-clipboard-list fa-lg mb-2"></i>
                                <span>Mes logs</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row g-3 mt-3">

            <!-- Scolarité payée -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm small-card h-100">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-money-bill-wave text-info mb-2"></i>
                        <div class="fw-semibold text-muted small">Scolarité payée</div>
                        <div class="fw-bold fs-6 text-dark">{{ number_format($totalPayeScolarite, 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>

            <!-- Inscriptions payées -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm small-card h-100">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-user-check text-success mb-2"></i>
                        <div class="fw-semibold text-muted small">Inscriptions payées</div>
                        <div class="fw-bold fs-6 text-dark">{{ number_format($totalPayeInscription, 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>

            <!-- Prévision annuelle -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm small-card h-100">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-chart-line text-primary mb-2"></i>
                        <div class="fw-semibold text-muted small">Prévision annuelle</div>
                        <div class="fw-bold fs-6 text-dark">{{ number_format($previsionScolarite + $previsionInscription, 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>

            <!-- Taux de paiement -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm small-card h-100">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-percentage text-warning mb-2"></i>
                        <div class="fw-semibold text-muted small">Taux de paiement</div>
                        <div class="fw-bold fs-6 text-dark">{{ $tauxPaiement }}%</div>
                    </div>
                </div>
            </div>

        </div>


    </div>

    <!-- Colonne droite simplifiée -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 rounded-1 mb-3 p-3">
            <h6 class="mb-3">Répartition par niveau</h6>

            <div class="d-flex justify-content-between align-items-center">
                <!-- Primaire -->
                <div class="text-center flex-fill me-2 p-2 bg-light rounded">
                    <i class="fas fa-school fa-2x text-primary mb-1"></i>
                    <p class="mb-0 fw-bold">{{ $inscritsPrimaire }}</p>
                    <small class="text-muted">Primaire</small>
                </div>

                <!-- Collège -->
                <div class="text-center flex-fill ms-2 p-2 bg-light rounded">
                    <i class="fas fa-graduation-cap fa-2x text-info mb-1"></i>
                    <p class="mb-0 fw-bold">{{ $inscritsCollege }}</p>
                    <small class="text-muted">Collège</small>
                </div>
            </div>

        </div>


        <!-- 📅 Calendrier & Heure -->
        <div class="card shadow-sm border-0 rounded-1 mb-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-semibold text-primary">
                    <i class="fas fa-calendar-alt me-2"></i> Calendrier & Heure
                </h6>
                <div id="liveClock" class="fw-bold text-secondary fs-6"></div>
            </div>

            <!-- En-tête du calendrier (mois + année) -->
            <div class="calendar-header text-center mb-2">
                <span id="calendarMonth" class="fw-bold text-dark"></span>
            </div>

            <!-- Conteneur du calendrier -->
            <div id="miniCalendar" class="calendar-container"></div>
        </div>
    </div>
</div>

