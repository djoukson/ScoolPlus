<div class="sp-grid">
    <div class="sp-card sp-kpi sp-info"><i class="fas fa-money-bill-wave"></i><div class="l">Scolarité payée</div><div class="v">{{ number_format($totalPayeScolarite, 0, ',', ' ') }} FCFA</div></div>
    <div class="sp-card sp-kpi sp-success"><i class="fas fa-user-check"></i><div class="l">Inscriptions payées</div><div class="v">{{ number_format($totalPayeInscription, 0, ',', ' ') }} FCFA</div></div>
    <div class="sp-card sp-kpi sp-primary"><i class="fas fa-chart-line"></i><div class="l">Prévision annuelle</div><div class="v">{{ number_format($previsionScolarite + $previsionInscription, 0, ',', ' ') }} FCFA</div></div>
    <div class="sp-card sp-kpi sp-warning"><i class="fas fa-percentage"></i><div class="l">Taux de paiement</div><div class="v">{{ $tauxPaiement }}%</div>
        <div class="sp-bar mt-2" style="--c:var(--amber)"><span data-w="{{ min(100,(float)$tauxPaiement) }}"></span></div></div>
</div>
