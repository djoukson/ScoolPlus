<div class="sp-stat sp-{{ $tone }}">
    <div class="sp-stat-ico"><i class="fas {{ $icon }}"></i></div>
    <div>
        <div class="sp-stat-val" @if(is_numeric($value)) data-count="{{ $value }}" @endif>{{ $value }}</div>
        <div class="sp-stat-lbl">{{ $label }}</div>
        @if(!empty($href))<a href="{{ $href }}" class="sp-stat-link">{{ $more ?? 'Voir plus' }} <i class="fas fa-arrow-right"></i></a>@endif
    </div>
</div>
