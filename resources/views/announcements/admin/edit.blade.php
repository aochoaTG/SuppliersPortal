<div class="card m-0">
  <div class="card-body">
    <h5 class="card-title">Editar Comunicado</h5>
    <hr>

    <form id="formEditComunicado" method="POST"
          action="{{ route('admin.announcements.update', $announcement) }}"
          enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <!-- Primera fila: Título y Prioridad -->
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Título de comunicado *</label>
                <input type="text" name="title" class="form-control form-control-sm"
                       maxlength="50" minlength="5" required autocomplete="off"
                       value="{{ old('title', $announcement->title) }}"
                       placeholder="Ingrese el título del comunicado">
                <div class="form-text"><small>5-50 caracteres</small></div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Prioridad *</label>
                <select name="priority" class="form-select form-select-sm" required>
                    <option value="1" @selected(old('priority', $announcement->priority) == 1)>🟢 Baja</option>
                    <option value="2" @selected(old('priority', $announcement->priority) == 2)>🔵 Normal</option>
                    <option value="3" @selected(old('priority', $announcement->priority) == 3)>🟡 Alta</option>
                    <option value="4" @selected(old('priority', $announcement->priority) == 4)>🔴 Urgente</option>
                </select>
                <div class="form-text"><small>Importancia del comunicado</small></div>
            </div>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
            <label class="form-label">Descripción *</label>
            <textarea name="description" class="form-control form-control-sm"
                      maxlength="500" minlength="10" rows="2" required
                      placeholder="Descripción breve del comunicado">{{ old('description', $announcement->description) }}</textarea>
            <div class="form-text"><small>10-500 caracteres</small></div>
        </div>

        <!-- Segunda fila: Fechas -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Fecha publicación *</label>
                <input type="datetime-local" name="published_at"
                       class="form-control form-control-sm" required
                       value="{{ old('published_at', optional($announcement->published_at)->format('Y-m-d\TH:i')) }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Visible hasta</label>
                <input type="datetime-local" name="visible_until"
                       class="form-control form-control-sm"
                       value="{{ old('visible_until', optional($announcement->visible_until)->format('Y-m-d\TH:i')) }}">
                <div class="form-text"><small>Opcional - posterior a publicación</small></div>
            </div>
        </div>

        <!-- Tercera fila: Archivo y Estado -->
        <div class="row align-items-end">
            <div class="col-md-6 mb-3">
                <label class="form-label">Portada</label>

                @if($announcement->cover_url)
                    <div class="mb-2">
                        <img src="{{ $announcement->cover_url }}" alt="cover" class="img-fluid rounded" style="max-height: 120px;">
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="remove_cover" name="remove_cover" value="1">
                        <label for="remove_cover" class="form-check-label">Quitar portada actual</label>
                    </div>
                @endif

                <input type="file" name="cover" class="form-control form-control-sm"
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       data-max-size="5242880">
                <div class="form-text"><small>JPG, PNG, GIF, WEBP - Max 5MB</small></div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" class="form-check-input" id="is_active"
                           name="is_active" value="1"
                           @checked(old('is_active', $announcement->is_active)) role="switch">
                    <label class="form-check-label" for="is_active">Comunicado activo</label>
                </div>
                <div class="form-text"><small>Desactivar para ocultar temporalmente</small></div>
            </div>
        </div>

        <!-- Campo honeypot -->
        <div style="opacity: 0; position: absolute; left: -9999px;">
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <!-- Botones -->
        <div class="text-end pt-2 border-top">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                <i class="ti ti-x me-1"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-sm btn-primary" id="submitBtnEdit">
                <i class="ti ti-check me-1"></i>Guardar cambios
            </button>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formEditComunicado');
    const fileInput = form.querySelector('input[name="cover"]');
    const maxSize = fileInput.getAttribute('data-max-size');

    // Validación de tamaño de archivo
    fileInput.addEventListener('change', function() {
        if (this.files[0] && this.files[0].size > maxSize) {
            alert('El archivo excede el tamaño máximo permitido (5MB)');
            this.value = '';
        }
    });

    // Validación de fechas
    form.addEventListener('submit', function(e) {
        const publishedAt = form.published_at.value ? new Date(form.published_at.value) : null;
        const visibleUntil = form.visible_until.value ? new Date(form.visible_until.value) : null;

        if (publishedAt && visibleUntil && visibleUntil <= publishedAt) {
            e.preventDefault();
            alert('La fecha "Visible hasta" debe ser posterior a la fecha de publicación');
            return false;
        }

        // Honeypot
        if (form.website.value) {
            e.preventDefault();
            return false;
        }

        // Prevenir doble envío
        const submitBtn = document.getElementById('submitBtnEdit');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Guardando...';
    });

    // Validación en tiempo real para fechas
    const publishedInput = form.querySelector('input[name="published_at"]');
    const visibleInput = form.querySelector('input[name="visible_until"]');

    function validateDates() {
        if (visibleInput.value && publishedInput.value) {
            const v = new Date(visibleInput.value);
            const p = new Date(publishedInput.value);
            if (v <= p) {
                visibleInput.setCustomValidity('Debe ser posterior a la fecha de publicación');
            } else {
                visibleInput.setCustomValidity('');
            }
        } else {
            visibleInput.setCustomValidity('');
        }
    }
    publishedInput.addEventListener('change', validateDates);
    visibleInput.addEventListener('change', validateDates);
});
</script>

<style>
.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}
.form-text small { font-size: 0.75rem; }
.card-title { font-size: 1.1rem; font-weight: 600; }
.form-label { font-weight: 500; font-size: 0.9rem; margin-bottom: 0.3rem; }
.btn-sm { padding: 0.25rem 0.75rem; font-size: 0.875rem; }
</style>
