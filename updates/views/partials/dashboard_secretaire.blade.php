
<div class="container py-4">
    <div class="row g-3">
        <!-- Left Column -->
        <div class="col-md-8">
            <div class="row g-3">
                <!-- Élèves actifs -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-success text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $totalEleves }}</h4>
                                <p class="mb-0 small">Élèves actifs</p>
                            </div>
                            <i class="fas fa-user-graduate fa-2x opacity-75"></i>
                        </div>
                        <div class="card-footer bg-transparent border-0 p-2 text-end">
                            <a href="{{ route('eleves.index') }}" class="text-white fw-bold small">
                                Voir plus <span class="fas fa-arrow-circle-right ms-1"></span>
                            </a>
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

                <!-- Professeurs -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-warning text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h4 class="fw-bold mb-1">{{$professeurs}}</h4>
                                <p class="mb-0 small">Professeurs</p>
                            </div>
                            <i class="fas fa-chalkboard-teacher fa-2x opacity-75"></i>
                        </div>
                        <div class="card-footer bg-transparent border-0 p-2 text-end">
                            <a href="{{ route('enseignants.index') }}" class="text-white fw-bold small">
                                Voir plus <span class="fas fa-arrow-circle-right ms-1"></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Année scolaire -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-primary text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $anneeActive->nom ?? 'N/A' }}</h4>
                                <p class="mb-0 small">Année scolaire</p>
                            </div>
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Classes -->
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-secondary text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $totalClasses ?? ''}}</h4>
                                <p class="mb-0 small">Classes</p>
                            </div>
                            <i class="fas fa-school fa-2x opacity-75"></i>
                        </div>
                        <div class="card-footer bg-transparent border-0 p-2 text-end">
                            <a href="{{ route('classes.index') }}" class="text-white fw-bold small">
                                Voir plus <span class="fas fa-arrow-circle-right ms-1"></span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 🔹 Actions rapides -->
                <div class="card shadow-sm border-0 rounded-3 mb-3 overflow-hidden">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-bolt me-2 text-warning"></i>Actions rapides
                        </h6>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2">
                            <!-- Ajouter Élève -->
                            <div class="col-4">
                                <a href="{{ route('eleves.index') }}" class="quick-action bg-success-subtle text-success shadow-sm">
                                    <i class="fas fa-user-plus fa-lg mb-2"></i>
                                    <span>Élève</span>
                                </a>
                            </div>

                            <!-- Nouvelle Classe -->
                            <div class="col-4">
                                <a href="{{ route('classes.index') }}" class="quick-action bg-primary-subtle text-primary shadow-sm">
                                    <i class="fas fa-school fa-lg mb-2"></i>
                                    <span>Classe</span>
                                </a>
                            </div>

                            <!-- Ajouter Professeur -->
                            <div class="col-4">
                                <a href="{{ route('enseignants.index') }}" class="quick-action bg-warning-subtle text-warning shadow-sm">
                                    <i class="fas fa-chalkboard-teacher fa-lg mb-2"></i>
                                    <span>Professeur</span>
                                </a>
                            </div>

                            <!-- Gérer Inscriptions -->
                            <div class="col-4">
                                <a href="{{ route('eleves.index') }}" class="quick-action bg-info-subtle text-info shadow-sm">
                                    <i class="fas fa-clipboard-list fa-lg mb-2"></i>
                                    <span>Inscriptions</span>
                                </a>
                            </div>

                            <!-- Voir Logs -->
                            <div class="col-4">
                                <a href="{{ route('userlogs') }}" class="quick-action bg-secondary-subtle text-secondary shadow-sm">
                                    <i class="fas fa-history fa-lg mb-2"></i>
                                    <span>Logs</span>
                                </a>
                            </div>

                            <!-- Paramètres -->
                            <div class="col-4">
                                <a href="{{ route('settings.index') }}" class="quick-action bg-danger-subtle text-danger shadow-sm">
                                    <i class="fas fa-cog fa-lg mb-2"></i>
                                    <span>Paramètres</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Right Column -->
        <div class="col-md-4">
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
            <div class="card shadow-sm border-0 rounded-1 mb-3 p-3">

                <h6 class="mb-3">État des inscriptions</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <!-- Élèves inscrits -->
                    <div class="text-center flex-fill me-2 p-2 bg-light rounded">
                        <i class="fas fa-user-check fa-2x text-success mb-1"></i>
                        <p class="mb-0 fw-bold">{{ $inscrits }}</p>
                        <small class="text-muted">Inscrits</small>
                    </div>

                    <!-- Élèves non inscrits -->
                    <div class="text-center flex-fill ms-2 p-2 bg-light rounded">
                        <i class="fas fa-user-times fa-2x text-danger mb-1"></i>
                        <p class="mb-0 fw-bold">{{ $nonInscrits }}</p>
                        <small class="text-muted">Non inscrits</small>
                    </div>
                </div>

                <!-- Lien vers liste des classes -->
                <div class="mt-3 text-end">
                    <a href="{{ route('listeclasses.index') }}" class="fw-bold text-primary small">
                        Voir la liste <span class="fas fa-arrow-circle-right ms-1"></span>
                    </a>
                </div>
            </div>
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

                <!-- Lien vers classes -->
                <div class="mt-3 text-end">
                    <a href="{{ route('listeclasses.index') }}" class="fw-bold text-primary small">
                        Détails des classes <span class="fas fa-arrow-circle-right ms-1"></span>
                    </a>
                </div>
            </div>



        </div>


    </div>
</div>

