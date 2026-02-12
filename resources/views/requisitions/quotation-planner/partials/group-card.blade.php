{{--
    Componente: Tarjeta de Grupo
    
    Props:
    - $group: QuotationGroup
--}}

<div class="group-card card mb-3" 
     data-group-id="{{ $group->id }}">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            {{-- Nombre del grupo (editable) --}}
            <div class="flex-grow-1">
                <h5 class="mb-0 group-name-display">
                    <i class="ti ti-folder me-2"></i>
                    {{ $group->name }}
                </h5>
            </div>

            {{-- Acciones --}}
            <div class="btn-group btn-group-sm">
                <button type="button" 
                        class="btn btn-sm btn-light edit-group-btn" 
                        data-group-id="{{ $group->id }}"
                        title="Editar nombre">
                    <i class="ti ti-edit"></i>
                </button>
                <button type="button" 
                        class="btn btn-sm btn-danger delete-group-btn" 
                        data-group-id="{{ $group->id }}"
                        title="Eliminar grupo">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        {{-- Sugerencias de proveedores --}}
        @if($group->hasMixedCategories())
        <div class="alert alert-warning py-2 mb-3" role="alert">
            <i class="ti ti-alert-triangle me-1"></i>
            <small>
                <strong>Categorías mixtas:</strong> Este grupo incluye productos de diferentes categorías.
                Algunos proveedores pueden ofrecer mejores precios separándolas.
            </small>
        </div>
        @endif

        {{-- TODO: Sugerencias de proveedores (próxima iteración) --}}
        {{-- 
        <div class="mb-3">
            <h6 class="text-muted mb-2">
                <i class="ti ti-users me-1"></i>
                Proveedores sugeridos (3):
            </h6>
            <ul class="list-unstyled mb-0 small">
                <li>• Office Depot ⭐⭐⭐⭐⭐</li>
                <li>• Lumen ⭐⭐⭐⭐</li>
                <li>• OfficeMax ⭐⭐⭐</li>
            </ul>
        </div>
        --}}

        {{-- Lista de partidas agrupadas --}}
        <div class="mb-3">
            <h6 class="text-muted mb-2">
                <i class="ti ti-list me-1"></i>
                Partidas en este grupo ({{ $group->items->count() }}):
            </h6>

            <div class="group-items-list border rounded p-2" style="max-height: 250px; overflow-y: auto;">
                @forelse($group->items as $item)
                <div class="group-item-mini d-flex justify-content-between align-items-center p-2 mb-1 bg-light rounded"
                     data-item-id="{{ $item->id }}">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2" style="font-size: 0.7rem;">#{{ $loop->iteration }}</span>
                            <small class="fw-bold">{{ Str::limit($item->product->name ?? 'Sin nombre', 40) }}</small>
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="ti ti-package" style="font-size: 0.8rem;"></i> {{ $item->quantity }} × 
                            ${{ number_format($item->unit_price, 2) }} = 
                            <strong>${{ number_format($item->quantity * $item->unit_price, 2) }}</strong>
                        </div>
                    </div>
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger remove-item-from-group-btn"
                            data-group-id="{{ $group->id }}"
                            data-item-id="{{ $item->id }}"
                            title="Quitar del grupo">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                @empty
                <div class="text-center py-3 text-muted">
                    <i class="ti ti-inbox"></i>
                    <p class="mb-0 small">Grupo vacío</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Zona de drop para agregar más partidas --}}
        <div class="group-items-drop-zone border border-2 border-dashed rounded p-3 text-center" 
             data-group-id="{{ $group->id }}"
             style="background-color: #f8f9fa; min-height: 80px; display: flex; align-items: center; justify-content: center;">
            <small class="text-muted">
                <i class="ti ti-download"></i>
                Arrastra más partidas aquí
            </small>
        </div>

        {{-- Notas del grupo (si existen) --}}
        @if($group->notes)
        <div class="mt-3">
            <small class="text-muted">
                <i class="ti ti-note me-1"></i>
                <strong>Notas:</strong> {{ $group->notes }}
            </small>
        </div>
        @endif
    </div>
</div>

{{-- Script específico para este componente --}}
@once
@push('scripts')
<script>
$(document).ready(function() {
    // Editar nombre del grupo
    $(document).on('click', '.edit-group-btn', function() {
        const groupId = $(this).data('group-id');
        const currentName = $(this).closest('.card-header').find('.group-name-display').text().trim();
        
        Swal.fire({
            title: 'Editar Nombre del Grupo',
            input: 'text',
            inputValue: currentName.replace(/📦\s*/g, '').trim(),
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar un nombre';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Actualizar el nombre visualmente
                $(this).closest('.card-header').find('.group-name-display').html(
                    '<i class="ti ti-folder me-2"></i>' + result.value
                );
                
                // Notificar que debe guardar
                Swal.fire({
                    icon: 'info',
                    title: 'Nombre actualizado',
                    text: 'No olvides guardar la estrategia',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // Eliminar grupo
    $(document).on('click', '.delete-group-btn', function() {
        const groupId = $(this).data('group-id');
        const groupCard = $(this).closest('.group-card');
        
        Swal.fire({
            title: '¿Eliminar grupo?',
            text: 'Las partidas regresarán a la lista de partidas sin agrupar',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                groupCard.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Mostrar mensaje vacío si no hay grupos
                    if ($('.group-card').length === 0) {
                        $('#groupsList').html(`
                            <div class="text-center py-5" id="emptyGroupsMessage">
                                <i class="ti ti-folder-plus text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Aún no has creado grupos</p>
                                <p class="text-muted small">Arrastra partidas aquí o haz clic en "Nuevo Grupo"</p>
                            </div>
                        `);
                    }
                    
                    updateSummary();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Grupo eliminado',
                        text: 'No olvides guardar los cambios',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });
    });

    // Remover partida de grupo
    $(document).on('click', '.remove-item-from-group-btn', function() {
        const groupId = $(this).data('group-id');
        const itemId = $(this).data('item-id');
        const itemElement = $(this).closest('.group-item-mini');
        
        Swal.fire({
            title: '¿Quitar partida del grupo?',
            text: 'La partida regresará a la lista de partidas sin agrupar',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                itemElement.fadeOut(300, function() {
                    $(this).remove();
                    updateSummary();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Partida removida',
                        text: 'No olvides guardar los cambios',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });
    });
});
</script>
@endpush
@endonce