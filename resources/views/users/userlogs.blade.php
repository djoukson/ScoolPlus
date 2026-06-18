@extends('layouts.app')

@section('title', 'Journal des Actions')
@section('page-title', 'Journal des Actions')

@section('content')
    <div class="container py-4">
        <div class="card border-0 shadow-sm rounded-3">

            {{-- 🔹 Header --}}
            <form method="GET" class="d-flex align-items-center gap-2 mb-3">
                <select name="start" id="startDate" class="form-select form-select-sm">
                    <option value="">Date de début</option>
                    @foreach($dates as $date)
                        <option value="{{ $date }}" {{ request('start') == $date ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>

                <span class="text-muted small">à</span>

                <select name="end" id="endDate" class="form-select form-select-sm">
                    <option value="">Date de fin</option>
                    @foreach($dates as $date)
                        <option value="{{ $date }}" {{ request('end') == $date ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-filter me-1"></i> Filtrer
                </button>

                <button type="button" id="btnPrint" class="btn btn-sm btn-outline-dark">
                    <i class="fas fa-print me-1"></i> Imprimer
                </button>
            </form>


            {{-- 🔹 Table --}}
            <div class="card-body p-0" style="background-color: #ffffff;">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0" id="logsTable"
                           style="font-family: 'Roboto Mono', monospace; font-size: 0.6rem;">
                        <thead class="border-bottom bg-light text-uppercase text-secondary small">
                        <tr>
                            <th style="width: 15%;">Date / Heure</th>
                            <th style="width: 15%;">Utilisateur</th>
                            <th style="width: 15%;">Action</th>
                            <th style="width: 45%;">Description</th>
                            <th style="width: 10%;">IP</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logs as $log)
                            <tr class="log-entry border-bottom">
                                <td class="text-muted">{{ $log->created_at->format('d M Y · H:i') }}</td>
                                <td class="text-dark">{{ strtoupper($log->user->name ?? '—') }}</td>
                                <td class="text-primary">{{ strtoupper($log->action) }}</td>
                                <td class="text-secondary">{{ $log->description }}</td>
                                <td class="text-muted small">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>Aucun log enregistré.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 🔹 Pagination --}}
            @if($logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-center">
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection


@push('styles')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;600&display=swap');

        .log-entry td {
            padding: 0.8rem 1rem;
            vertical-align: middle;
        }

        .log-entry:hover {
            background-color: #f8f9fb;
            transition: background-color 0.2s ease-in-out;
        }

        th {
            letter-spacing: 0.3px;
        }

        .table {
            border-spacing: 0 2px;
        }

        .badge {
            font-size: 0.60rem;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            #logsTable, #logsTable * {
                visibility: visible;
            }
            #logsTable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
@endpush


@push('scripts')
    <script>
        // 🧾 Impression de l’intervalle sélectionné
        $('#btnPrint').on('click', function () {
            const start = $('#startDate').val();
            const end = $('#endDate').val();

            if (!start || !end) {
                alert("Veuillez sélectionner un intervalle de dates avant d'imprimer.");
                return;
            }

            // Redirection vers une page d’impression filtrée
            const url = "{{ route('logs.print') }}" + "?start=" + start + "&end=" + end;
            window.open(url, '_blank');
        });
    </script>
@endpush
