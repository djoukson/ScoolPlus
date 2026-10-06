@php $i=(int)$inscrits; $n=(int)$nonInscrits; $t=max($i+$n,1); @endphp
<div class="sp-card">
    <div class="sp-h">État des inscriptions</div>
    <div class="sp-grid" style="grid-template-columns:1fr 1fr">
        <div class="sp-kpi sp-success"><i class="fas fa-user-check fa-lg"></i><div class="v">{{ $inscrits }}</div><div class="l">Inscrits</div></div>
        <div class="sp-kpi sp-danger"><i class="fas fa-user-times fa-lg"></i><div class="v">{{ $nonInscrits }}</div><div class="l">Non inscrits</div></div>
    </div>
    <div class="sp-bar mt-3" style="--c:var(--mint)"><span data-w="{{ round($i/$t*100) }}"></span></div>
    <div class="text-end mt-3"><a href="{{ route('listeclasses.index') }}" class="sp-link">Voir la liste <i class="fas fa-arrow-right ms-1"></i></a></div>
</div>
