<div class="sp-layout">
  <div class="sp-stack">
    <div class="sp-grid">
      @include('partials.dash.stat',['tone'=>'success','icon'=>'fa-user-graduate','value'=>$totalEleves,'label'=>'Élèves inscrits'])
      @include('partials.dash.stat',['tone'=>'danger','icon'=>'fa-female','value'=>$filles,'label'=>'Filles'])
      @include('partials.dash.stat',['tone'=>'info','icon'=>'fa-male','value'=>$garcons,'label'=>'Garçons'])
    </div>
    <div class="sp-grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">
      @include('partials.dash.stat',['tone'=>'success','icon'=>'fa-money-bill-wave','value'=>'Frais','label'=>'Gestion des paiements Scolaires','href'=>route('paiements.index'),'more'=>'Gérer'])
      @include('partials.dash.stat',['tone'=>'primary','icon'=>'fa-concierge-bell','value'=>'Services','label'=>'Souscriptions et paiements des services','href'=>route('services.paiementsouscriptions')])
    </div>
    @include('partials.dash.finance')
    <div class="sp-card">
      <div class="sp-h"><span><i class="fas fa-bolt me-2" style="color:var(--amber)"></i>Actions rapides</span></div>
      <div class="sp-acts">
        @include('partials.dash.action',['tone'=>'success','icon'=>'fa-list','label'=>'Type de Frais scolaires','href'=>route('frais.index')])
        @include('partials.dash.action',['tone'=>'info','icon'=>'fa-coins','label'=>'Montants scolaires','href'=>route('montants_frais.index')])
        @include('partials.dash.action',['tone'=>'warning','icon'=>'fa-money-check-alt','label'=>'Paiements des frais scolaires','href'=>route('paiements.index')])
        @include('partials.dash.action',['tone'=>'primary','icon'=>'fa-edit','label'=>'Souscriptions aux services','href'=>route('services.souscriptions')])
        @include('partials.dash.action',['tone'=>'danger','icon'=>'fa-chart-line','label'=>'État des souscriptions','href'=>route('services.paiementsouscriptions')])
        @include('partials.dash.action',['tone'=>'secondary','icon'=>'fa-clipboard-list','label'=>'Mes logs','href'=>route('userlogs', Auth::id())])
      </div>
    </div>
  </div>
  <div class="sp-stack">
    @include('partials.dash.levels',['noLink'=>true,'inscritsLycee'=>null,'__nolycee'=>1])
    @include('partials.dash.calendar')
    @include('partials.dash.gender')
  </div>
</div>
