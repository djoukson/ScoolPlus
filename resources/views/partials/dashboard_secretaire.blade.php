<div class="sp-layout">
  <div class="sp-stack">
    <div class="sp-grid">
      @include('partials.dash.stat',['tone'=>'success','icon'=>'fa-user-graduate','value'=>$totalEleves,'label'=>'Élèves actifs','href'=>route('eleves.index')])
      @include('partials.dash.stat',['tone'=>'danger','icon'=>'fa-female','value'=>$filles,'label'=>'Filles'])
      @include('partials.dash.stat',['tone'=>'info','icon'=>'fa-male','value'=>$garcons,'label'=>'Garçons'])
      @include('partials.dash.stat',['tone'=>'warning','icon'=>'fa-chalkboard-teacher','value'=>$professeurs,'label'=>'Professeurs','href'=>route('enseignants.index')])
      @include('partials.dash.stat',['tone'=>'primary','icon'=>'fa-calendar-alt','value'=>$anneeActive->nom ?? 'N/A','label'=>'Année scolaire'])
      @include('partials.dash.stat',['tone'=>'secondary','icon'=>'fa-school','value'=>$totalClasses ?? '','label'=>'Classes','href'=>route('classes.index')])
    </div>
    <div class="sp-card">
      <div class="sp-h"><span><i class="fas fa-bolt me-2" style="color:var(--amber)"></i>Actions rapides</span></div>
      <div class="sp-acts">
        @include('partials.dash.action',['tone'=>'success','icon'=>'fa-user-plus','label'=>'Élève','href'=>route('eleves.index')])
        @include('partials.dash.action',['tone'=>'primary','icon'=>'fa-school','label'=>'Classe','href'=>route('classes.index')])
        @include('partials.dash.action',['tone'=>'warning','icon'=>'fa-chalkboard-teacher','label'=>'Professeur','href'=>route('enseignants.index')])
        @include('partials.dash.action',['tone'=>'info','icon'=>'fa-clipboard-list','label'=>'Inscriptions','href'=>route('eleves.index')])
        @include('partials.dash.action',['tone'=>'secondary','icon'=>'fa-history','label'=>'Logs','href'=>route('userlogs')])
        @include('partials.dash.action',['tone'=>'danger','icon'=>'fa-cog','label'=>'Paramètres','href'=>route('settings.index')])
      </div>
    </div>
  </div>
  <div class="sp-stack">
    @include('partials.dash.calendar')
    @include('partials.dash.enrol')
    @include('partials.dash.gender')
    @include('partials.dash.levels',['inscritsLycee'=>null])
  </div>
</div>
