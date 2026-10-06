<div class="sp-layout">
  <div class="sp-stack">
    <div class="sp-grid">
      @include('partials.dash.stat',['tone'=>'success','icon'=>'fa-user-graduate','value'=>$totalEleves,'label'=>'Élèves'])
      @include('partials.dash.stat',['tone'=>'danger','icon'=>'fa-female','value'=>$filles,'label'=>'Filles'])
      @include('partials.dash.stat',['tone'=>'info','icon'=>'fa-male','value'=>$garcons,'label'=>'Garçons'])
      @include('partials.dash.stat',['tone'=>'primary','icon'=>'fa-calendar-alt','value'=>$anneeActive->nom ?? 'N/A','label'=>'Année scolaire'])
      @include('partials.dash.stat',['tone'=>'secondary','icon'=>'fa-chalkboard','value'=>$nombreClasses,'label'=>'Classes assignées'])
      @include('partials.dash.stat',['tone'=>'warning','icon'=>'fa-book','value'=>$nombreMatieres,'label'=>'Matières enseignées'])
    </div>
    <div class="sp-card">
      <div class="sp-h"><span><i class="fas fa-bolt me-2" style="color:var(--amber)"></i>Actions rapides</span></div>
      <div class="sp-acts">
        @include('partials.dash.action',['tone'=>'success','icon'=>'fa-tasks','label'=>'Évaluations','href'=>route('evaluations.index')])
        @include('partials.dash.action',['tone'=>'primary','icon'=>'fa-clock','label'=>'Emploi du temps','href'=>route('monemplois.index')])
        @include('partials.dash.action',['tone'=>'warning','icon'=>'fa-book-open','label'=>'Mes matières','href'=>route('matieres.mesmatieres')])
        @include('partials.dash.action',['tone'=>'info','icon'=>'fa-user-times','label'=>'Absences','href'=>'#'])
        @include('partials.dash.action',['tone'=>'danger','icon'=>'fa-star','label'=>'Notes','href'=>route('evaluations.index')])
        @include('partials.dash.action',['tone'=>'secondary','icon'=>'fa-envelope','label'=>'Messages','href'=>'#'])
      </div>
    </div>
  </div>
  <div class="sp-stack">
    @include('partials.dash.calendar')
    @include('partials.dash.gender')
    <div class="sp-card">
      <div class="sp-h">Mes évaluations récentes</div>
      @if(count($evaluations) > 0)
        <ul class="sp-list">
          @foreach($evaluations as $eval)
            <li><i class="fas fa-clipboard-check"></i>{{ $eval->titre }} — <span class="text-muted">{{ $eval->classe->nom ?? '' }}</span></li>
          @endforeach
        </ul>
      @else
        <p class="text-muted small mb-0">Aucune évaluation enregistrée.</p>
      @endif
    </div>
  </div>
</div>
