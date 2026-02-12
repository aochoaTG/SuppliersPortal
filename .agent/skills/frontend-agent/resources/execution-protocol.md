# Protocolo de Ejecución - Frontend Agent

## Fase 1: Preparación y Contexto

**Objetivo**: Entender qué vista debo crear y qué datos consumo.

### 1.1 Leo Contrato API (si existe)
```bash
# Busco en:
.agent/skills/_shared/api-contracts/[modulo]-contract.md
```

**Extraigo**:
- Endpoints disponibles (GET, POST, PUT, DELETE)
- Estructura de request/response
- Campos del formulario
- Validaciones esperadas

### 1.2 Identifico Tipo de Vista

| Tipo | Componentes | Ejemplo |
|------|-------------|---------|
| **Index** | DataTable + Botón Nuevo + Modal | Lista de proveedores |
| **Create** | Formulario + Validación | Nuevo proveedor |
| **Edit** | Formulario precargado | Editar proveedor |
| **Show** | Card con detalles | Ver proveedor |
| **Wizard** | Multi-step form | Cotización 5 pasos |
| **Dashboard** | Cards + Charts | Inicio |

**En TotalGas, preferimos**: Index con modal (no páginas separadas para create/edit)

---

## Fase 2: Estructura de Archivo Blade

**Objetivo**: Crear template con layout Zircos correcto.

### Template Base: Index con DataTable

**Archivo**: `resources/views/[modulo]/[entidad]/index.blade.php`
```blade
@extends('layouts.app')

@section('title', 'Proveedores')

@section('breadcrumb')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Proveedores</h2>
                <div class="text-muted mt-1">
                    <ol class="breadcrumb breadcrumb-bullets">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Proveedores</li>
                    </ol>
                </div>
            </div>
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProveedor">
                    <i class="ti ti-plus"></i> Nuevo Proveedor
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-xl">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Proveedores</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabla-proveedores">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>RFC</th>
                                    <th>Contacto</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Crear/Editar --}}
@include('proveedores.partials.modal-form')

@endsection

@push('scripts')
<script src="{{ asset('js/proveedores/index.js') }}"></script>
@endpush
```

### Modal Form (Partial)

**Archivo**: `resources/views/[modulo]/[entidad]/partials/modal-form.blade.php`
```blade
<div class="modal fade" id="modalProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formProveedor" novalidate>
                @csrf
                <input type="hidden" id="proveedor_id" name="id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProveedorLabel">Nuevo Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                            <div class="invalid-feedback">Por favor ingrese el nombre</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="rfc" class="form-label">RFC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="rfc" name="rfc" maxlength="13" required>
                            <div class="invalid-feedback">RFC inválido</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contacto" class="form-label">Contacto</label>
                            <input type="text" class="form-control" id="contacto" name="contacto">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                <label class="form-check-label" for="activo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm d-none" id="spinner"></span>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## Fase 3: JavaScript - DataTable y CRUD

**Archivo**: `resources/js/[modulo]/index.js`
```javascript
$(document).ready(function() {
    
    // =========================================
    // 1. CONFIGURACIÓN DATATABLE
    // =========================================
    const tabla = $('#tabla-proveedores').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/proveedores',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr) {
                console.error('Error al cargar datos:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos'
                });
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'nombre', name: 'nombre' },
            { data: 'rfc', name: 'rfc' },
            { data: 'contacto', name: 'contacto', orderable: false },
            { 
                data: 'activo', 
                name: 'activo',
                render: function(data) {
                    return data ? 
                        '<span class="badge bg-success">Activo</span>' :
                        '<span class="badge bg-secondary">Inactivo</span>';
                }
            },
            { 
                data: 'acciones', 
                name: 'acciones', 
                orderable: false, 
                searchable: false,
                className: 'text-end'
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
        },
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>'
    });

    // =========================================
    // 2. ABRIR MODAL PARA NUEVO
    // =========================================
    $('[data-bs-target="#modalProveedor"]').on('click', function() {
        limpiarFormulario();
        $('#modalProveedorLabel').text('Nuevo Proveedor');
        $('#proveedor_id').val('');
        $('#modalProveedor').modal('show');
    });

    // =========================================
    // 3. EDITAR (delegado para botones dinámicos)
    // =========================================
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: `/proveedores/${id}`,
            type: 'GET',
            success: function(response) {
                $('#modalProveedorLabel').text('Editar Proveedor');
                $('#proveedor_id').val(response.id);
                $('#nombre').val(response.nombre);
                $('#rfc').val(response.rfc);
                $('#contacto').val(response.contacto);
                $('#telefono').val(response.telefono);
                $('#direccion').val(response.direccion);
                $('#activo').prop('checked', response.activo);
                $('#modalProveedor').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el registro'
                });
            }
        });
    });

    // =========================================
    // 4. GUARDAR (Create/Update)
    // =========================================
    $('#formProveedor').on('submit', function(e) {
        e.preventDefault();
        
        // Validación Bootstrap 5
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        
        const id = $('#proveedor_id').val();
        const url = id ? `/proveedores/${id}` : '/proveedores';
        const method = id ? 'PUT' : 'POST';
        
        // Loading state
        $('#btnGuardar').prop('disabled', true);
        $('#spinner').removeClass('d-none');
        
        $.ajax({
            url: url,
            type: method,
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#modalProveedor').modal('hide');
                tabla.ajax.reload(null, false);
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message || 'Operación exitosa',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                limpiarFormulario();
            },
            error: function(xhr) {
                let mensaje = 'Ocurrió un error al guardar';
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    mensaje = Object.values(errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error de validación',
                    html: mensaje
                });
            },
            complete: function() {
                $('#btnGuardar').prop('disabled', false);
                $('#spinner').addClass('d-none');
            }
        });
    });

    // =========================================
    // 5. ELIMINAR
    // =========================================
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        Swal.fire({
            title: '¿Estás seguro?',
            html: `Se eliminará el proveedor: <strong>${nombre}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/proveedores/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        tabla.ajax.reload(null, false);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let mensaje = 'No se pudo eliminar el registro';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensaje = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: mensaje
                        });
                    }
                });
            }
        });
    });

    // =========================================
    // 6. FUNCIONES AUXILIARES
    // =========================================
    function limpiarFormulario() {
        $('#formProveedor')[0].reset();
        $('#formProveedor').removeClass('was-validated');
        $('#proveedor_id').val('');
    }
    
    // Cerrar modal y limpiar
    $('#modalProveedor').on('hidden.bs.modal', function() {
        limpiarFormulario();
    });
});
```

---

## Fase 4: Validaciones y Mejoras UX

### 4.1 Validación jQuery (Opcional, alternativa a checkValidity())
```javascript
// Si prefieres jQuery Validation
$('#formProveedor').validate({
    rules: {
        nombre: {
            required: true,
            minlength: 3,
            maxlength: 255
        },
        rfc: {
            required: true,
            minlength: 12,
            maxlength: 13
        }
    },
    messages: {
        nombre: {
            required: 'El nombre es obligatorio',
            minlength: 'Mínimo 3 caracteres',
            maxlength: 'Máximo 255 caracteres'
        },
        rfc: 'RFC inválido'
    },
    errorClass: 'text-danger',
    submitHandler: function(form) {
        // Lógica de AJAX aquí
    }
});
```

### 4.2 Feedback Visual en Inputs
```javascript
// Feedback en tiempo real
$('#rfc').on('blur', function() {
    const rfc = $(this).val().toUpperCase();
    
    // Validación regex RFC México
    const rfcRegex = /^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/;
    
    if (rfc && !rfcRegex.test(rfc)) {
        $(this).addClass('is-invalid');
        $(this).next('.invalid-feedback').text('Formato de RFC inválido');
    } else {
        $(this).removeClass('is-invalid');
    }
});
```

### 4.3 Loading State en DataTable
```javascript
// Mostrar spinner mientras carga
tabla.on('processing.dt', function (e, settings, processing) {
    if (processing) {
        $('.dataTables_processing').html(
            '<div class="spinner-border text-primary" role="status"></div>'
        );
    }
});
```

---

## Fase 5: Responsive y Accesibilidad

### 5.1 DataTable Responsive
```javascript
// Configuración responsive avanzada
$('#tabla-proveedores').DataTable({
    // ... otras opciones
    responsive: {
        details: {
            type: 'column',
            target: 'tr'
        }
    },
    columnDefs: [{
        className: 'dtr-control',
        orderable: false,
        targets: 0
    }]
});
```

### 5.2 Accesibilidad (ARIA)
```blade
{{-- En modal --}}
<div class="modal fade" id="modalProveedor" tabindex="-1" 
     aria-labelledby="modalProveedorLabel" aria-hidden="true">
    
    {{-- Labels con for --}}
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" id="nombre" aria-required="true" aria-describedby="nombreHelp">
    <small id="nombreHelp" class="form-text">Ingrese el nombre completo</small>
</div>
```

---

## Fase 6: Verificación Final

### Checklist Frontend

- [ ] **Vista renderiza** sin errores Blade
- [ ] **Layout correcto** (extends Zircos)
- [ ] **Breadcrumb** funcional
- [ ] **DataTable** carga datos desde API
- [ ] **Botón Nuevo** abre modal limpio
- [ ] **Modal Create** guarda correctamente (POST)
- [ ] **Modal Edit** carga y actualiza (PUT)
- [ ] **Eliminar** pide confirmación y ejecuta (DELETE)
- [ ] **Validaciones** muestran mensajes claros
- [ ] **SweetAlert** funciona en success/error
- [ ] **No hay errores** en consola JavaScript
- [ ] **Responsive** funciona en móvil
- [ ] **Tokens CSRF** en todos los AJAX
- [ ] **Loading states** en botones

### Pruebas Manuales
```bash
# 1. Abrir en navegador
http://localhost/proveedores

# 2. Verificar:
- DataTable muestra datos
- Click "Nuevo" → modal abre
- Llenar form → Submit → Success alert
- Recargar tabla → nuevo registro aparece
- Click "Editar" → modal precarga datos
- Modificar → Submit → Update success
- Click "Eliminar" → confirmación → Delete success
- Buscar en DataTable → filtra
- Ordenar columnas → funciona
```

---

## 🎨 Convenciones de Estilo TotalGas

### Colores Bootstrap
```css
/* Usos estándar */
.btn-primary      /* Acciones principales (Guardar, Nuevo) */
.btn-secondary    /* Cancelar */
.btn-danger       /* Eliminar */
.btn-warning      /* Editar */
.btn-info         /* Ver detalles */
.btn-success      /* Aprobar */
```

### Iconos Tabler
```html
<i class="ti ti-plus"></i>      <!-- Nuevo -->
<i class="ti ti-pencil"></i>    <!-- Editar -->
<i class="ti ti-trash"></i>     <!-- Eliminar -->
<i class="ti ti-eye"></i>       <!-- Ver -->
<i class="ti ti-check"></i>     <!-- Aprobar -->
<i class="ti ti-x"></i>         <!-- Cancelar -->
<i class="ti ti-download"></i>  <!-- Descargar -->
```

@if($proveedor->activo)
    <span class="badge bg-success">Activo</span>
@else
    <span class="badge bg-secondary">Inactivo</span>
@endif

{{-- Estados de orden --}}
<span class="badge bg-warning">Pendiente</span>
<span class="badge bg-info">En Proceso</span>
<span class="badge bg-success">Completado</span>
<span class="badge bg-danger">Cancelado</span>
