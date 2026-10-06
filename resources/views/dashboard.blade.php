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

    @include('partials.dash.styles')

    <div class="container py-4 sp">

        @if(in_array($role, ['admin', 'directeur']))
            @include('partials.dashboard_admin_directeur', [
                'totalEleves' => $totalEleves, 'filles' => $filles, 'garcons' => $garcons,
                'professeurs' => $professeurs, 'anneeActive' => $anneeActive, 'totalClasses' => $totalClasses,
                'nouveaux' => $nouveaux, 'inscrits' => $inscrits, 'nonInscrits' => $nonInscrits,
                'inscritsPrimaire' => $inscritsPrimaire, 'inscritsCollege' => $inscritsCollege,
                'inscritsLycee' => $inscritsLycee, 'totalclassesyear' => $totalclassesyear
            ])
        @elseif($role === 'comptable')
            @include('partials.dashboard_comptable', [
                'totalEleves' => $totalEleves, 'filles' => $filles, 'garcons' => $garcons, 'anneeActive' => $anneeActive,
                'totalPayeScolarite' => $totalPayeScolarite, 'totalPayeInscription' => $totalPayeInscription,
                'previsionScolarite' => $previsionScolarite, 'previsionInscription' => $previsionInscription,
                'statistiquesClasses' => $statistiquesClasses, 'totalSouscriptions' => $totalSouscriptions,
                'souscriptionsParService' => $souscriptionsParService, 'tauxPaiement' => $tauxPaiement,
                'classeTop' => $classeTop, 'inscritsPrimaire' => $inscritsPrimaire,
                'inscritsLycee' => $inscritsLycee, 'inscritsCollege' => $inscritsCollege
            ])
        @elseif($role === 'professeur')
            @include('partials.dashboard_enseignant', [
                'totalEleves' => $totalEleves, 'filles' => $filles, 'garcons' => $garcons, 'anneeActive' => $anneeActive,
                'totalClasses' => $totalClasses, 'nombreMatieres' => $nombreMatieres, 'nombreClasses' => $nombreClasses,
                'mesMatieres' => $mesMatieres ?? [], 'evaluations' => $evaluations ?? [],
            ])
        @elseif($role === 'secretaire')
            @include('partials.dashboard_secretaire', [
                'totalEleves' => $totalEleves, 'filles' => $filles, 'garcons' => $garcons, 'professeurs' => $professeurs,
                'anneeActive' => $anneeActive, 'totalClasses' => $totalClasses, 'nouveaux' => $nouveaux,
                'inscrits' => $inscrits, 'nonInscrits' => $nonInscrits, 'inscritsPrimaire' => $inscritsPrimaire,
                'inscritsCollege' => $inscritsCollege, 'inscritsLycee' => $inscritsLycee,
            ])
        @elseif($role === 'parent')
            @include('partials.dashboard_parent', [
                'anneeActive' => $anneeActive, 'enfantsData' => $enfantsData, 'totalPrevu' => $totalPrevu,
                'totalPaye' => $totalPaye, 'totalReste' => $totalReste, 'tauxGlobal' => $tauxGlobal,
                'messagesNonLus' => $messagesNonLus, 'notificationsRecentes' => $notificationsRecentes,
            ])
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const clock = document.getElementById('liveClock');
            if (clock) {
                const tick = () => clock.textContent = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
                tick(); setInterval(tick, 1000);
            }
            const calendar = document.getElementById('miniCalendar'), header = document.getElementById('calendarMonth');
            if (!calendar || !header) return;
            const today = new Date(), year = today.getFullYear(), month = today.getMonth();
            const mois = ["janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre"];
            header.textContent = `${mois[month]} ${year}`;
            const firstDay = new Date(year, month, 1).getDay(), daysInMonth = new Date(year, month + 1, 0).getDate();
            const add = (txt, cls) => { const d = document.createElement('div'); if (cls) d.className = cls; d.textContent = txt; calendar.appendChild(d); return d; };
            ['L','M','M','J','V','S','D'].forEach(j => add(j, 'calendar-day'));
            for (let i = 0; i < (firstDay === 0 ? 6 : firstDay - 1); i++) add('');
            for (let i = 1; i <= daysInMonth; i++) add(i, 'calendar-date' + (i === today.getDate() ? ' calendar-today' : ''));
        });
    </script>
@endsection
