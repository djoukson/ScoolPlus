<div class="container py-4">
    <div class="row g-3">

        <!-- Colonne principale -->
        <div class="col-md-8">
            <div class="row g-3">

                <!-- Total élèves -->
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-success text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $totalEleves }}</h4>
                                <p class="mb-0 small">Élèves</p>
                            </div>
                            <i class="fas fa-user-graduate fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Filles -->
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-danger text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $filles }}</h4>
                                <p class="mb-0 small">Filles</p>
                            </div>
                            <i class="fas fa-female fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Garçons -->
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-info text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $garcons }}</h4>
                                <p class="mb-0 small">Garçons</p>
                            </div>
                            <i class="fas fa-male fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Année scolaire -->
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-primary text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $anneeActive->nom ?? 'N/A' }}</h4>
                                <p class="mb-0 small">Année scolaire</p>
                            </div>
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Classes attribuées -->
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-secondary text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $nombreClasses }}</h4>
                                <p class="mb-0 small">Classes assignées</p>
                            </div>
                            <i class="fas fa-chalkboard fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- Nombre de matières -->
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 rounded-1 stat-box bg-warning text-white h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">{{ $nombreMatieres }}</h4>
                                <p class="mb-0 small">Matières enseignées</p>
                            </div>
                            <i class="fas fa-book fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>

                <!-- 🔹 Actions rapides -->
                <div class="card shadow-sm border-0 rounded-3 mt-3">
                    <div class="card-header bg-white border-0">
                        <h6 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-bolt me-2 text-warning"></i>Actions rapides
                        </h6>
                    </div>

                    <div class="card-body p-3">
                        <div class="row g-2">

                            <div class="col-4">
                                <a href="{{ route('evaluations.index') }}" class="quick-action bg-success-subtle text-success">
                                    <i class="fas fa-tasks fa-lg mb-2"></i>
                                    <span>Évaluations</span>
                                </a>
                            </div>

                            <div class="col-4">
                                <a href="{{ route('monemplois.index') }}" class="quick-action bg-primary-subtle text-primary">
                                    <i class="fas fa-clock fa-lg mb-2"></i>
                                    <span>Emploi du temps</span>
                                </a>
                            </div>

                            <div class="col-4">
                                <a href="{{ route('matieres.mesmatieres') }}" class="quick-action bg-warning-subtle text-warning">
                                    <i class="fas fa-book-open fa-lg mb-2"></i>
                                    <span>Mes matières</span>
                                </a>
                            </div>

                            <div class="col-4">
                                <a href="#" class="quick-action bg-info-subtle text-info">
                                    <i class="fas fa-user-times fa-lg mb-2"></i>
                                    <span>Absences</span>
                                </a>
                            </div>

                            <div class="col-4">
                                <a href="{{ route('evaluations.index') }}" class="quick-action bg-danger-subtle text-danger">
                                    <i class="fas fa-star fa-lg mb-2"></i>
                                    <span>Notes</span>
                                </a>
                            </div>

                            <div class="col-4">
                                <a href="#" class="quick-action bg-secondary-subtle text-secondary">
                                    <i class="fas fa-envelope fa-lg mb-2"></i>
                                    <span>Messages</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Colonne droite -->
        <div class="col-md-4">
            <!-- Calendrier -->
            <div class="card shadow-sm border-0 rounded-1 mb-3 p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-semibold text-primary">
                        <i class="fas fa-calendar-alt me-2"></i> Calendrier & Heure
                    </h6>
                    <div id="liveClock" class="fw-bold text-secondary fs-6"></div>
                </div>
                <div class="calendar-header text-center mb-2">
                    <span id="calendarMonth" class="fw-bold text-dark"></span>
                </div>
                <div id="miniCalendar" class="calendar-container"></div>
            </div>

            <!-- Statistiques simplifiées -->
            <div class="card shadow-sm border-0 rounded-1 mb-3 p-3">
                <h6 class="mb-3 fw-bold text-primary">Mes évaluations récentes</h6>
                @if(count($evaluations) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($evaluations as $eval)
                            <li class="list-group-item small">
                                <i class="fas fa-clipboard-check text-success me-2"></i>
                                {{ $eval->titre }} — <span class="text-muted">{{ $eval->classe->nom ?? '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">Aucune évaluation enregistrée.</p>
                @endif
            </div>
        </div>
    </div>
</div>
