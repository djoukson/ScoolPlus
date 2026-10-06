@php $f=(int)$filles; $g=(int)$garcons; $t=max($f+$g,1); @endphp
<div class="sp-card">
    <div class="sp-h">Répartition filles / garçons</div>
    <div class="d-flex align-items-center gap-4">
        <div class="sp-donut" style="--p:{{ round($f/$t*100) }}"><div>{{ round($f/$t*100) }}%<small>filles</small></div></div>
        <div class="sp-leg"><div><i style="background:var(--coral)"></i>Filles <b>{{ $filles }}</b></div><div><i style="background:var(--sky)"></i>Garçons <b>{{ $garcons }}</b></div></div>
    </div>
</div>
