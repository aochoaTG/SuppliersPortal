{{--
    Modal: Crear Grupo de Cotización
--}}

<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createGroupModalLabel">
                    <i class="ti ti-folder-plus me-2"></i>
                    Crear Nuevo Grupo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createGroupForm">
                    <div class="mb-3">
                        <label for="groupName" class="form-label">
                            Nombre del Grupo <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="groupName" 
                               name="name"
                               placeholder="Ej: Equipo de Oficina, Papelería, Mobiliario..."
                               required
                               maxlength="100">
                        <div class="form-text">
                            Elige un nombre descriptivo que identifique las partidas que agruparás.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="groupNotes" class="form-label">
                            Notas (Opcional)
                        </label>
                        <textarea class="form-control" 
                                  id="groupNotes" 
                                  name="notes"
                                  rows="3"
                                  placeholder="Ej: Buscar descuento por volumen, Priorizar entrega rápida..."></textarea>
                        <div class="form-text">
                            Agrega notas o consideraciones especiales para este grupo.
                        </div>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <i class="ti ti-info-circle me-2"></i>
                        <small>
                            Después de crear el grupo, podrás agregar partidas arrastrándolas 
                            o usando los checkboxes.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveGroupBtn">
                    <i class="ti ti-check"></i> Crear Grupo
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
$(document).ready(function() {
    // Guardar nuevo grupo
    $('#saveGroupBtn').click(function() {
        const form = $('#createGroupForm')[0];
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const name = $('#groupName').val();
        const notes = $('#groupNotes').val();

        const saveBtn = $(this);
        const originalText = saveBtn.html();
        
        saveBtn.prop('disabled', true).html('<i class="ti ti-loader rotating"></i> Creando...');

        $.ajax({
            url: `{{ route('requisitions.quotation-planner.groups.create', $requisition) }}`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                notes: notes,
                item_ids: [] // Vacío por ahora
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Grupo creado!',
                        text: 'Ahora puedes agregar partidas al grupo',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo crear el grupo'
                });
                
                saveBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Limpiar formulario al cerrar modal
    $('#createGroupModal').on('hidden.bs.modal', function() {
        $('#createGroupForm')[0].reset();
    });

    // Focus automático al abrir modal
    $('#createGroupModal').on('shown.bs.modal', function() {
        $('#groupName').focus();
    });
});
</script>
@endpush
@endonce