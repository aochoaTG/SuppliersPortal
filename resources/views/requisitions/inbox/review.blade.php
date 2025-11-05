@extends('layouts.zircos')

@section('title', 'Bandeja de Revisión')
@section('page.title', 'Bandeja de Revisión')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('requisitions.inbox.review') }}">Revisión</a></li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-clipboard-check"></i> Pendientes de revisión</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Compañía</th>
                        <th>Centro</th>
                        <th>Depto</th>
                        <th>Solicitante</th>
                        <th>Fecha requerida</th>
                        <th>Monto</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->folio }}</td>
                            <td>{{ $r->company->name ?? '—' }}</td>
                            <td>{{ ($r->costCenter->code ? '[' . $r->costCenter->code . '] ' : '') . ($r->costCenter->name ?? '—') }}
                            </td>
                            <td>{{ $r->department->name ?? '—' }}</td>
                            <td>{{ $r->requester->name ?? '—' }}</td>
                            <td>{{ optional($r->required_date)->format('d/m/Y') }}</td>
                            <td>${{ number_format($r->amount_requested, 2) }}</td>
                            <td>
                                @if ($r->status === 'on_hold')
                                    <span class="badge bg-warning"
                                        @if ($r->on_hold_reason) data-bs-toggle="tooltip" title="{{ $r->on_hold_reason }}" @endif>
                                        En espera
                                    </span>
                                    @if ($r->onHoldUser)
                                        <small class="text-muted d-block">
                                            por {{ $r->onHoldUser->name }} ·
                                            {{ optional($r->on_hold_at)->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="badge bg-info">En revisión</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('requisitions.mark-reviewed', $r) }}"
                                    class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success"><i class="ti ti-checkbox"></i> Marcar
                                        revisada</button>
                                </form>
                                {{-- Botón "En espera" que abre el modal --}}
                                <button type="button" class="btn btn-sm btn-warning js-hold-btn"
                                    data-id="{{ $r->id }}" data-folio="{{ $r->folio }}">
                                    <i class="ti ti-hand-stop"></i> En espera
                                </button>
                                {{-- Botón "Rechazar" con Swal --}}
                                <button type="button" class="btn btn-sm btn-outline-danger js-reject-btn"
                                    data-id="{{ $r->id }}" data-folio="{{ $r->folio }}">
                                    <i class="ti ti-x"></i> Rechazar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-muted text-center">Sin pendientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $rows->links() }}
        </div>
    </div>
    {{-- MODAL: Motivo de espera --}}
    <div class="modal fade" id="modalHold" tabindex="-1" aria-labelledby="modalHoldLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="formHold">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning-subtle">
                        <h5 class="modal-title" id="modalHoldLabel">
                            <i class="ti ti-hand-stop"></i> Poner requisición en espera
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="hold_reason" class="form-label">Motivo o comentario</label>
                            <textarea name="reason" id="hold_reason" class="form-control" rows="3" required></textarea>
                            <input type="hidden" name="requisition_id" id="hold_requisition_id">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="ti ti-hand-stop"></i> Confirmar En Espera
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('modalHold');
            const form = document.getElementById('formHold');
            const idInput = document.getElementById('hold_requisition_id');
            const reasonInput = document.getElementById('hold_reason');

            // Inicializa modal Bootstrap
            const modal = new bootstrap.Modal(modalEl);

            // Al hacer clic en "En espera"
            document.querySelectorAll('.js-hold-btn').forEach(btn => {
                btn.addEventListener('click', e => {
                    const id = btn.dataset.id;
                    const folio = btn.dataset.folio;
                    idInput.value = id;
                    reasonInput.value = '';
                    modalEl.querySelector('.modal-title').innerHTML =
                        `<i class="ti ti-hand-stop"></i> Poner en espera — <strong>${folio}</strong>`;
                    modal.show();
                });
            });

            // Envía el form con POST dinámico
            form.addEventListener('submit', e => {
                e.preventDefault();
                const id = idInput.value;
                const reason = reasonInput.value.trim();
                if (!reason) {
                    alert('Por favor, escribe un motivo.');
                    return;
                }

                fetch(`{{ url('requisitions') }}/${id}/hold`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            reason
                        })
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error al poner en espera.');
                        return res.json().catch(() => ({}));
                    })
                    .then(() => {
                        modal.hide();
                        location.reload();
                    })
                    .catch(err => alert(err.message));
            });
        });
    </script>

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
