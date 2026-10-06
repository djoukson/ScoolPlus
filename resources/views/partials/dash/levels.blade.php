@php $p=(int)$inscritsPrimaire; $c=(int)$inscritsCollege; $l=isset($inscritsLycee)?(int)$inscritsLycee:0; $t=max($p+$c+$l,1); @endphp
<div class="sp-card">
    <div class="sp-h">Répartition par niveau</div>
    <div class="sp-row" style="--c:var(--cobalt)"><div><span><i class="fas fa-school me-2"></i>Primaire</span><b>{{ $inscritsPrimaire }}</b></div><div class="sp-bar"><span data-w="{{ round($p/$t*100) }}"></span></div></div>
    <div class="sp-row" style="--c:var(--sky)"><div><span><i class="fas fa-graduation-cap me-2"></i>Collège</span><b>{{ $inscritsCollege }}</b></div><div class="sp-bar"><span data-w="{{ round($c/$t*100) }}"></span></div></div>
    @if(isset($inscritsLycee))
    <div class="sp-row" style="--c:var(--amber)"><div><span><i class="fas fa-graduation-cap me-2"></i>Lycée</span><b>{{ $inscritsLycee }}</b></div><div class="sp-bar"><span data-w="{{ round($l/$t*100) }}"></span></div></div>
    @endif
    @if(!isset($noLink))<div class="text-end"><a href="{{ route('listeclasses.index') }}" class="sp-link">Détails des classes <i class="fas fa-arrow-right ms-1"></i></a></div>@endif
</div>
