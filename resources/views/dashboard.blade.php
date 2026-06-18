@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Tableau de bord')

@section('content')

    @php
        if (!Auth::check()) {
            echo '<script>window.location.href="' . route('login') . '";</script>';
            exit;
        }
        $role = Auth::user()->role;


    @endphp

    <div class="container py-4">

        {{-- ✅ Dashboard pour ADMIN & DIRECTEUR --}}
        @if(in_array($role, ['admin', 'directeur']))

            @include('partials.dashboard_admin_directeur', [
                'totalEleves' => $totalEleves,
                'filles' => $filles,
                'garcons' => $garcons,
                'professeurs' => $professeurs,
                'anneeActive' => $anneeActive,
                'classes' => $classes,
                'nouveaux' => $nouveaux,
                'inscrits' => $inscrits,
                'nonInscrits' => $nonInscrits,
                'inscritsPrimaire' => $inscritsPrimaire,
                'inscritsCollege'=> $inscritsCollege,
                'inscritsLycee'=> $inscritsLycee,
                'totalclassesyear'=> $totalclassesyear
            ])

            {{-- ✅ Dashboard pour COMPTABLE --}}
        @elseif($role === 'comptable')

            @include('partials.dashboard_comptable', [
                'totalEleves' => $totalEleves,
                'filles' => $filles,
                'garcons' => $garcons,
                'anneeActive' => $anneeActive,
                'totalPayeScolarite' => $totalPayeScolarite,
                'totalPayeInscription' => $totalPayeInscription,
                'previsionScolarite' => $previsionScolarite,
                'previsionInscription' => $previsionInscription,
                'statistiquesClasses' => $statistiquesClasses,
                'totalSouscriptions' => $totalSouscriptions,
                'souscriptionsParService' => $souscriptionsParService,
                'tauxPaiement' => $tauxPaiement,
                'classeTop' => $classeTop,
                'inscritsPrimaire' => $inscritsPrimaire,
                'inscritsLycee'=> $inscritsLycee,
                'inscritsCollege'=> $inscritsCollege
            ])

            {{-- ✅ Dashboard pour ENSEIGNANT --}}
        @elseif($role === 'professeur')

            @include('partials.dashboard_enseignant', [
               'totalEleves' => $totalEleves,
                'filles' => $filles,
                'garcons' => $garcons,
                'anneeActive' => $anneeActive,
                'classes' => $classes,
                'nombreMatieres' => $nombreMatieres,
                'nombreClasses' => $nombreClasses,
                'mesMatieres' => $mesMatieres ?? [],
                'evaluations' => $evaluations ?? [],
            ])

            {{-- ✅ Dashboard pour SECRÉTAIRE --}}
        @elseif($role === 'secretaire')

            @include('partials.dashboard_secretaire', [
                 'totalEleves' => $totalEleves,
                'filles' => $filles,
                'garcons' => $garcons,
                'professeurs' => $professeurs,
                'anneeActive' => $anneeActive,
                'classes' => $classes,
                'nouveaux' => $nouveaux,
                'inscrits' => $inscrits,
                'nonInscrits' => $nonInscrits,
                'inscritsPrimaire' => $inscritsPrimaire,
                'inscritsCollege'=> $inscritsCollege,
                'inscritsLycee'=> $inscritsLycee,
            ])

        @endif
    </div>

    <style>
        .calendar-header {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            text-transform: capitalize;
        }

        .calendar-container {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            font-size: 0.85rem;
            user-select: none;
        }

        .calendar-container div {
            text-align: center;
            padding: 8px 0;
            border-radius: 6px;
            transition: 0.2s;
        }

        .calendar-day {
            font-weight: 600;
            color: #6b7280;
        }

        .calendar-date {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #374151;
        }

        .calendar-date:hover {
            background-color: #e0e7ff;
            color: #4338ca;
            cursor: pointer;
        }

        .calendar-today {
            background: linear-gradient(135deg, #6366f1, #22c55e);
            color: white;
            font-weight: 600;
            border: none;
        }

        #liveClock {
            font-family: "Segoe UI", sans-serif;
            color: #334155;
            letter-spacing: 0.5px;
        }

        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 10px;
            padding: 18px 10px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease-in-out;
            background: #f8f9fa;
        }

        .quick-action:hover {
            transform: translateY(-4px);
            background-color: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .stat-box {
            transition: all 0.25s ease-in-out;
            cursor: pointer;
        }

        .stat-box:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 18px rgba(0,0,0,0.12) !important;
        }

        .bg-success { background: linear-gradient(135deg, #28a745, #218838); }
        .bg-danger { background: linear-gradient(135deg, #dc3545, #bd2130); }
        .bg-info { background: linear-gradient(135deg, #17a2b8, #0d6efd); }
        .bg-warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
        .bg-primary { background: linear-gradient(135deg, #007bff, #0056b3); }
        .bg-secondary { background: linear-gradient(135deg, #6c757d, #5a6268); }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function updateClock() {
                const now = new Date();
                const options = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
                document.getElementById('liveClock').textContent = now.toLocaleTimeString([], options);
            }
            updateClock();
            setInterval(updateClock, 1000);

            const calendar = document.getElementById('miniCalendar');
            const header = document.getElementById('calendarMonth');
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();
            const mois = ["janvier", "février", "mars", "avril", "mai", "juin","juillet", "août", "septembre", "octobre", "novembre", "décembre"];
            header.textContent = `${mois[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const jours = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];

            jours.forEach(j => {
                const el = document.createElement('div');
                el.classList.add('calendar-day');
                el.textContent = j;
                calendar.appendChild(el);
            });

            for (let i = 0; i < (firstDay === 0 ? 6 : firstDay - 1); i++) {
                const blank = document.createElement('div');
                calendar.appendChild(blank);
            }

            for (let i = 1; i <= daysInMonth; i++) {
                const dateEl = document.createElement('div');
                dateEl.classList.add('calendar-date');
                dateEl.textContent = i;
                if (i === today.getDate()) dateEl.classList.add('calendar-today');
                calendar.appendChild(dateEl);
            }
        });
    </script>
@endsection
