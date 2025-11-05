<div class="card m-0">
  <div class="card-body">
    <h5 class="card-title">Crear Comunicado</h5>
    <hr>
    <form id="formCreateComunicado" method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <input type="hidden" name="form_token" value="{{ Str::random(40) }}">

        <!-- Primera fila: Título y Prioridad -->
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Título de comunicado *</label>
                <input type="text" name="title" class="form-control form-control-sm"
                       maxlength="50" minlength="5" required autocomplete="off"
                       placeholder="Ingrese el título del comunicado">
                <div class="form-text"><small>5-50 caracteres</small></div>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Prioridad *</label>
                <select name="priority" class="form-select form-select-sm" required>
                    <option value="1" selected>🟢 Baja</option>
                    <option value="2">🔵 Normal</option>
                    <option value="3">🟡 Alta</option>
                    <option value="4">🔴 Urgente</option>
                </select>
                <div class="form-text"><small>Importancia del comunicado</small></div>
            </div>
        </div>

        <!-- Descripción -->
        <div class="mb-3">
            <label class="form-label">Descripción *</label>
            <textarea name="description" class="form-control form-control-sm"
                     maxlength="500" minlength="10" rows="2" required
                     placeholder="Descripción breve del comunicado"></textarea>
            <div class="form-text"><small>10-500 caracteres</small></div>
        </div>

        <!-- Segunda fila: Fechas -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Fecha publicación *</label>
                <input type="datetime-local" name="published_at"
                       class="form-control form-control-sm" required
                       min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Visible hasta</label>
                <input type="datetime-local" name="visible_until"
                       class="form-control form-control-sm"
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                <div class="form-text"><small>Opcional - posterior a publicación</small></div>
            </div>
        </div>

        <!-- Tercera fila: Archivo y Estado -->
        <div class="row align-items-end">
            <div class="col-md-6 mb-3">
                <label class="form-label">Portada (opcional)</label>
                <input type="file" name="cover" class="form-control form-control-sm"
                       accept="image/jpeg,image/png,image/gif,image/webp"
                       data-max-size="5242880">
                <div class="form-text"><small>JPG, PNG, GIF, WEBP - Max 5MB</small></div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" class="form-check-input" id="is_active"
                           name="is_active" value="1" checked role="switch">
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
            <button type="submit" class="btn btn-sm btn-primary" id="submitBtn">
                <i class="ti ti-check me-1"></i>Guardar
            </button>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCreateComunicado');
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
        const publishedAt = new Date(form.published_at.value);
        const visibleUntil = form.visible_until.value ? new Date(form.visible_until.value) : null;

        // Validar que visible_until sea posterior a published_at
        if (visibleUntil && visibleUntil <= publishedAt) {
            e.preventDefault();
            alert('La fecha "Visible hasta" debe ser posterior a la fecha de publicación');
            return false;
        }

        // Validación honeypot
        if (form.website.value) {
            e.preventDefault();
            return false;
        }

        // Prevenir doble envío
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Guardando...';
    });

    // Validación en tiempo real para fechas
    const publishedInput = form.querySelector('input[name="published_at"]');
    const visibleInput = form.querySelector('input[name="visible_until"]');

    publishedInput.addEventListener('change', function() {
        if (visibleInput.value) {
            const visibleDate = new Date(visibleInput.value);
            const publishedDate = new Date(this.value);

            if (visibleDate <= publishedDate) {
                visibleInput.setCustomValidity('Debe ser posterior a la fecha de publicación');
            } else {
                visibleInput.setCustomValidity('');
            }
        }
    });
});
</script>

<style>
.form-control-sm, .form-select-sm {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}

.form-text small {
    font-size: 0.75rem;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
}

.form-label {
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}
</style>
