@extends('layouts.zircos')

@section('title', 'Bandeja de Aprobación')
@section('page.title', 'Bandeja de Aprobación')
@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-stamp"></i> Pendientes de aprobación</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Revisada por</th>
                        <th>Compañía</th>
                        <th>Centro</th>
                        <th>Monto</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->folio }}</td>
                            <td>{{ $r->reviewer->name ?? '—' }} <span
                                    class="text-muted small">{{ optional($r->reviewed_at)->format('d/m/Y H:i') }}</span></td>
                            <td>{{ $r->company->name ?? '—' }}</td>
                            <td>{{ ($r->costCenter->code ? '[' . $r->costCenter->code . '] ' : '') . ($r->costCenter->name ?? '—') }}
                            </td>
                            <td>${{ number_format($r->amount_requested, 2) }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('requisitions.approve', $r) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-primary"><i class="ti ti-stamp"></i> Aprobar</button>
                                </form>
                                {{-- Botón "Rechazar" con Swal --}}
                                <button type="button" class="btn btn-sm btn-outline-danger js-reject-btn"
                                    data-id="{{ $r->id }}" data-folio="{{ $r->folio }}">
                                    <i class="ti ti-x"></i> Rechazar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center">Sin pendientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $rows->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // === Rechazar con motivo (SweetAlert2) ===
            document.querySelectorAll('.js-reject-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const reqId = btn.dataset.id;
                    const folio = btn.dataset.folio;

                    const {
                        value: reason
                    } = await Swal.fire({
                        title: `Rechazar requisición ${folio}`,
                        input: 'textarea',
                        inputLabel: 'Motivo del rechazo',
                        inputPlaceholder: 'Escribe el motivo...',
                        inputAttributes: {
                            'aria-label': 'Motivo del rechazo'
                        },
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        confirmButtonText: 'Rechazar',
                        confirmButtonColor: '#d33',
                        preConfirm: (text) => {
                            if (!text) {
                                Swal.showValidationMessage(
                                    'Debes escribir un motivo');
                            }
                            return text;
                        }
                    });

                    if (!reason) return;

                    // Enviar el rechazo vía fetch POST
                    try {
                        const response = await fetch(`/requisitions/${reqId}/reject`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                reason
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Requisición rechazada',
                                text: data.message ||
                                    'Se registró el rechazo correctamente.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message ||
                                    'No se pudo rechazar la requisición.'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor.'
                        });
                    }
                });
            });
        });
    </script>
@endpush
