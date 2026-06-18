@extends('layouts.app')

@section('content')

    <!-- Modal -->
    <div class="modal fade show" id="missingModal" tabindex="-1" role="dialog" style="display:block; background:rgba(0,0,0,0.6);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fa fa-exclamation-triangle"></i> Notes manquantes détectées
                    </h5>
                </div>

                <div class="modal-body">

                    <p class="mb-3">
                        Certains élèves n'ont pas toutes leurs notes (Devoir et/ou Composition).
                        <br>Veuillez vérifier les matières concernées.
                    </p>

                    @foreach($manques as $eleve)
                        <div class="border rounded p-3 mb-3">
                            <strong class="text-primary">
                                {{ $eleve['eleve'] }}
                            </strong>

                            <ul class="mt-2">
                                @foreach($eleve['matieres_manquantes'] as $m)
                                    <li>
                                        <strong>{{ $m['matiere'] }}</strong> :
                                        @if(!$m['devoir'])
                                            <span class="text-danger">Devoir manquant</span>
                                        @endif
                                        @if(!$m['composition'])
                                            <span class="text-danger ms-2">Composition manquante</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                </div>

                <div class="modal-footer">
                    <!-- Aller aux notes -->
                    <a href="{{ route('evaluations.createByClasse',$idClasse) }}"
                       class="btn btn-danger">
                        Aller aux notes
                    </a>

                    @if(isset($inscriptionId))
                        {{-- CONTEXTE : APERÇU BULLETIN --}}
                        <a href="{{ route('bulletins.apercu', [
            'inscriptionId' => $inscriptionId,
            'decoupage' => $decoupageId,
            'skipCheck' => 1
        ]) }}"
                           class="btn btn-warning">
                            Continuer quand même
                        </a>
                    @else
                        {{-- CONTEXTE : BULLETIN PAR DÉCOUPAGE --}}
                        <a href="{{ route('bulletins.decoupage', [
            'classe' => $idClasse,
            'decoupage' => $decoupageId,
            'skipCheck' => 1
        ]) }}"
                           class="btn btn-warning">
                            Continuer quand même
                        </a>
                    @endif

                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">
                        Annuler
                    </button>


                </div>

            </div>
        </div>
    </div>

@endsection
