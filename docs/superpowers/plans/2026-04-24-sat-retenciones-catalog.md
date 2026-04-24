# Catálogo SAT Retenciones — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Exponer el catálogo `sat_retenciones` como sección CRUD en el sidebar del portal, accesible solo para `superadmin`.

**Architecture:** Sigue el patrón `CategoryController` del proyecto: Route::resource + datatable endpoint, Form Request compartido para store/update, vistas con partial de formulario reutilizable. Las rutas se registran en el grupo `middleware(['auth', 'lock', 'role:superadmin'])` que ya existe en `web.php`.

**Tech Stack:** Laravel 12, PHP 8.2, SQL Server, Yajra DataTables, Bootstrap 5, Tabler Icons, SweetAlert2, jQuery.

---

## Mapa de archivos

| Archivo | Acción |
|---|---|
| `app/Http/Controllers/SatRetencionController.php` | Crear |
| `app/Http/Requests/SaveSatRetencionRequest.php` | Crear |
| `resources/views/sat_retenciones/index.blade.php` | Crear |
| `resources/views/sat_retenciones/create.blade.php` | Crear |
| `resources/views/sat_retenciones/edit.blade.php` | Crear |
| `resources/views/sat_retenciones/partials/form.blade.php` | Crear |
| `routes/web.php` | Modificar — agregar import + 2 líneas de rutas |
| `resources/views/layouts/partials/sidebar-staff.blade.php` | Modificar — `$openConfiguration` + nueva `<li>` |

---

## Task 1: Rutas

**Files:**
- Modify: `routes/web.php`

- [ ] **Step 1: Agregar el import de `SatRetencionController` en el bloque `use` al inicio de `web.php`**

Localiza el bloque `use App\Http\Controllers\{` (línea ~5). Añade `SatRetencionController,` al final de la lista, antes del `};`:

```php
use App\Http\Controllers\{
    // ... controllers existentes ...
    CostCenterImportController,
    SatRetencionController,
};
```

- [ ] **Step 2: Agregar las rutas en el grupo superadmin existente**

Localiza el bloque que comienza en la línea ~501:
```php
Route::middleware(['auth', 'lock', 'role:superadmin'])->group(function () {
    Route::resource('approval-levels', ApprovalLevelController::class)
        ->only(['index', 'edit', 'update'])
        ->names('approval-levels');

    Route::get('/approvals/quotations', ...
    Route::post('/approvals/quotations/...
});
```

Añade las dos líneas nuevas **dentro** del mismo closure, después de las rutas existentes:

```php
Route::middleware(['auth', 'lock', 'role:superadmin'])->group(function () {
    Route::resource('approval-levels', ApprovalLevelController::class)
        ->only(['index', 'edit', 'update'])
        ->names('approval-levels');

    Route::get('/approvals/quotations', [QuotationApprovalController::class, 'index'])->name('approvals.quotations.index');
    Route::post('/approvals/quotations/{summary}/handle', [QuotationApprovalController::class, 'handle'])->name('approvals.quotations.handle');

    // SAT Retenciones
    Route::get('sat-retenciones/datatable', [SatRetencionController::class, 'datatable'])
        ->name('sat-retenciones.datatable');
    Route::resource('sat-retenciones', SatRetencionController::class)
        ->except(['show'])
        ->parameters(['sat-retenciones' => 'sat_retencion']);
});
```

- [ ] **Step 3: Verificar que las rutas existen**

```bash
php artisan route:list --name=sat-retenciones
```

Salida esperada (7 rutas):
```
GET|HEAD   sat-retenciones ................. sat-retenciones.index
GET|HEAD   sat-retenciones/create .......... sat-retenciones.create
POST       sat-retenciones ................. sat-retenciones.store
GET|HEAD   sat-retenciones/datatable ....... sat-retenciones.datatable
GET|HEAD   sat-retenciones/{sat_retencion}/edit .. sat-retenciones.edit
PUT|PATCH  sat-retenciones/{sat_retencion} .. sat-retenciones.update
DELETE     sat-retenciones/{sat_retencion} .. sat-retenciones.destroy
```

- [ ] **Step 4: Commit**

```bash
git add routes/web.php
git commit -m "feat: rutas CRUD sat-retenciones (superadmin)"
```

---

## Task 2: Form Request

**Files:**
- Create: `app/Http/Requests/SaveSatRetencionRequest.php`

- [ ] **Step 1: Crear el archivo**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSatRetencionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $retencion = $this->route('sat_retencion');

        $uniqueClave = Rule::unique('sat_retenciones', 'clave');
        if ($retencion !== null) {
            $uniqueClave->ignore($retencion);
        }

        return [
            'clave'                   => ['required', 'string', 'max:20', $uniqueClave],
            'nombre'                  => ['required', 'string', 'max:100'],
            'impuesto'                => ['required', Rule::in(['ISR', 'IVA'])],
            'porcentaje'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'porcentaje_display'      => ['required', 'string', 'max:100'],
            'base_calculo'            => ['required', 'string', 'max:255'],
            'aplica_cuando'           => ['required', 'string', 'max:255'],
            'base_legal'              => ['required', 'string', 'max:100'],
            'descripcion'             => ['required', 'string', 'max:255'],
            'requiere_cfdi_retencion' => ['required', 'boolean'],
            'notas'                   => ['nullable', 'string'],
            'activo'                  => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'clave.required'              => 'La clave es obligatoria.',
            'clave.max'                   => 'La clave no puede superar 20 caracteres.',
            'clave.unique'                => 'Ya existe una retención con esa clave.',
            'nombre.required'             => 'El nombre es obligatorio.',
            'impuesto.required'           => 'Selecciona el tipo de impuesto.',
            'impuesto.in'                 => 'El impuesto debe ser ISR o IVA.',
            'porcentaje.numeric'          => 'El porcentaje debe ser un número.',
            'porcentaje.min'              => 'El porcentaje no puede ser negativo.',
            'porcentaje.max'              => 'El porcentaje no puede superar 100.',
            'porcentaje_display.required' => 'El texto de porcentaje es obligatorio.',
            'base_calculo.required'       => 'La base de cálculo es obligatoria.',
            'aplica_cuando.required'      => 'El campo "aplica cuando" es obligatorio.',
            'base_legal.required'         => 'La base legal es obligatoria.',
            'descripcion.required'        => 'La descripción es obligatoria.',
        ];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Requests/SaveSatRetencionRequest.php
git commit -m "feat: SaveSatRetencionRequest validación store/update"
```

---

## Task 3: Controlador

**Files:**
- Create: `app/Http/Controllers/SatRetencionController.php`

- [ ] **Step 1: Crear el archivo**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSatRetencionRequest;
use App\Models\SatRetencion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SatRetencionController extends Controller
{
    public function index(): View
    {
        return view('sat_retenciones.index');
    }

    public function create(): View
    {
        $sat_retencion = new SatRetencion([
            'activo'                  => true,
            'requiere_cfdi_retencion' => true,
        ]);

        return view('sat_retenciones.create', compact('sat_retencion'));
    }

    public function store(SaveSatRetencionRequest $request): RedirectResponse
    {
        SatRetencion::create($request->validated());

        return redirect()
            ->route('sat-retenciones.index')
            ->with('success', 'Retención creada correctamente.');
    }

    public function edit(SatRetencion $sat_retencion): View
    {
        return view('sat_retenciones.edit', compact('sat_retencion'));
    }

    public function update(SaveSatRetencionRequest $request, SatRetencion $sat_retencion): RedirectResponse
    {
        $sat_retencion->update($request->validated());

        return redirect()
            ->route('sat-retenciones.index')
            ->with('success', 'Retención actualizada correctamente.');
    }

    public function destroy(SatRetencion $sat_retencion): RedirectResponse
    {
        $sat_retencion->delete();

        return redirect()
            ->route('sat-retenciones.index')
            ->with('success', 'Retención eliminada correctamente.');
    }

    public function datatable()
    {
        $query = SatRetencion::query();

        return DataTables::of($query)
            ->editColumn('impuesto', function ($row) {
                return $row->impuesto === 'ISR'
                    ? '<span class="badge bg-primary">ISR</span>'
                    : '<span class="badge bg-warning text-dark">IVA</span>';
            })
            ->editColumn('requiere_cfdi_retencion', function ($row) {
                return $row->requiere_cfdi_retencion
                    ? '<span class="badge bg-success">Sí</span>'
                    : '<span class="badge bg-secondary">No</span>';
            })
            ->editColumn('activo', function ($row) {
                return $row->activo
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-secondary">Inactivo</span>';
            })
            ->addColumn('actions', function ($row) {
                $editUrl   = route('sat-retenciones.edit', $row->id);
                $deleteUrl = route('sat-retenciones.destroy', $row->id);
                $entity    = e($row->clave . ' — ' . $row->nombre);

                return '<div class="d-flex justify-content-end gap-1">'
                    . '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ti ti-pencil"></i></a>'
                    . '<form action="' . $deleteUrl . '" method="POST" class="d-inline js-delete-form">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-btn" data-entity="' . $entity . '" title="Eliminar"><i class="ti ti-trash"></i></button>'
                    . '</form>'
                    . '</div>';
            })
            ->rawColumns(['impuesto', 'requiere_cfdi_retencion', 'activo', 'actions'])
            ->make(true);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/SatRetencionController.php
git commit -m "feat: SatRetencionController CRUD + datatable"
```

---

## Task 4: Vista — Partial de formulario

**Files:**
- Create: `resources/views/sat_retenciones/partials/form.blade.php`

- [ ] **Step 1: Crear la carpeta y el archivo**

```blade
{{-- Formulario parcial reutilizable para create y edit --}}
@php
    $activoChecked           = old('activo', $sat_retencion->activo ?? true) ? true : false;
    $cfdiChecked             = old('requiere_cfdi_retencion', $sat_retencion->requiere_cfdi_retencion ?? true) ? true : false;
    $impuestoSeleccionado    = old('impuesto', $sat_retencion->impuesto ?? '');
@endphp

<div class="row g-3">

    {{-- Clave --}}
    <div class="col-md-6">
        <label for="clave" class="form-label">Clave <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('clave') is-invalid @enderror"
               id="clave" name="clave"
               value="{{ old('clave', $sat_retencion->clave ?? '') }}"
               placeholder="ISR-HON, IVA-ARR…"
               maxlength="20">
        @error('clave')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Identificador único, máx. 20 caracteres.</div>
    </div>

    {{-- Nombre --}}
    <div class="col-md-6">
        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('nombre') is-invalid @enderror"
               id="nombre" name="nombre"
               value="{{ old('nombre', $sat_retencion->nombre ?? '') }}"
               placeholder="ISR Honorarios"
               maxlength="100">
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Impuesto --}}
    <div class="col-md-6">
        <label for="impuesto" class="form-label">Impuesto <span class="text-danger">*</span></label>
        <select class="form-select @error('impuesto') is-invalid @enderror" id="impuesto" name="impuesto">
            <option value="">— Seleccionar —</option>
            <option value="ISR" {{ $impuestoSeleccionado === 'ISR' ? 'selected' : '' }}>ISR</option>
            <option value="IVA" {{ $impuestoSeleccionado === 'IVA' ? 'selected' : '' }}>IVA</option>
        </select>
        @error('impuesto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Base legal --}}
    <div class="col-md-6">
        <label for="base_legal" class="form-label">Base legal <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('base_legal') is-invalid @enderror"
               id="base_legal" name="base_legal"
               value="{{ old('base_legal', $sat_retencion->base_legal ?? '') }}"
               placeholder="ISR Art. 106"
               maxlength="100">
        @error('base_legal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Porcentaje (numérico, nullable) --}}
    <div class="col-md-6">
        <label for="porcentaje" class="form-label">Porcentaje</label>
        <input type="number"
               class="form-control @error('porcentaje') is-invalid @enderror"
               id="porcentaje" name="porcentaje"
               value="{{ old('porcentaje', $sat_retencion->porcentaje ?? '') }}"
               placeholder="10.0000"
               step="0.0001" min="0" max="100">
        @error('porcentaje')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Dejar vacío si la tasa es variable.</div>
    </div>

    {{-- Porcentaje display --}}
    <div class="col-md-6">
        <label for="porcentaje_display" class="form-label">Texto de porcentaje <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('porcentaje_display') is-invalid @enderror"
               id="porcentaje_display" name="porcentaje_display"
               value="{{ old('porcentaje_display', $sat_retencion->porcentaje_display ?? '') }}"
               placeholder="10% o Variable — tabla Art. 96"
               maxlength="100">
        @error('porcentaje_display')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Texto legible que se mostrará al usuario.</div>
    </div>

    {{-- Base de cálculo --}}
    <div class="col-12">
        <label for="base_calculo" class="form-label">Base de cálculo <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('base_calculo') is-invalid @enderror"
               id="base_calculo" name="base_calculo"
               value="{{ old('base_calculo', $sat_retencion->base_calculo ?? '') }}"
               placeholder="Monto del pago"
               maxlength="255">
        @error('base_calculo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Aplica cuando --}}
    <div class="col-12">
        <label for="aplica_cuando" class="form-label">Aplica cuando <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('aplica_cuando') is-invalid @enderror"
               id="aplica_cuando" name="aplica_cuando"
               value="{{ old('aplica_cuando', $sat_retencion->aplica_cuando ?? '') }}"
               placeholder="Persona moral paga a persona física"
               maxlength="255">
        @error('aplica_cuando')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Descripción --}}
    <div class="col-12">
        <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
        <input type="text"
               class="form-control @error('descripcion') is-invalid @enderror"
               id="descripcion" name="descripcion"
               value="{{ old('descripcion', $sat_retencion->descripcion ?? '') }}"
               placeholder="Servicios profesionales (persona física)"
               maxlength="255">
        @error('descripcion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Notas --}}
    <div class="col-12">
        <label for="notas" class="form-label">Notas</label>
        <textarea class="form-control @error('notas') is-invalid @enderror"
                  id="notas" name="notas"
                  rows="3"
                  placeholder="Observaciones adicionales, condiciones especiales…">{{ old('notas', $sat_retencion->notas ?? '') }}</textarea>
        @error('notas')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Requiere CFDI de retención --}}
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="requiere_cfdi_retencion" value="0">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="requiere_cfdi_retencion" name="requiere_cfdi_retencion"
                   value="1" {{ $cfdiChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="requiere_cfdi_retencion">
                Requiere CFDI de retención
            </label>
        </div>
        <div class="form-text">Indica si se debe emitir CFDI de retenciones e información de pagos.</div>
    </div>

    {{-- Activo --}}
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="activo" value="0">
            <input class="form-check-input" type="checkbox" role="switch"
                   id="activo" name="activo"
                   value="1" {{ $activoChecked ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
        <div class="form-text">Las retenciones inactivas no aparecerán en selectores.</div>
    </div>

</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/sat_retenciones/partials/form.blade.php
git commit -m "feat: partial de formulario sat_retenciones"
```

---

## Task 5: Vista — Index (listado DataTable)

**Files:**
- Create: `resources/views/sat_retenciones/index.blade.php`

- [ ] **Step 1: Crear el archivo**

```blade
@extends('layouts.zircos')

@section('title', 'Retenciones SAT')
@section('page.title', 'Retenciones SAT')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Retenciones SAT</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ti ti-receipt-tax me-1"></i> Retenciones SAT</h5>
    </div>

    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive">
            <table id="satRetencionesTable" class="table table-bordered table-hover w-100">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px;">Clave</th>
                        <th>Nombre</th>
                        <th style="width: 80px;" class="text-center">Impuesto</th>
                        <th style="width: 160px;">Porcentaje</th>
                        <th style="width: 90px;" class="text-center">CFDI Ret.</th>
                        <th style="width: 80px;" class="text-center">Activo</th>
                        <th style="width: 100px;" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.js-delete-btn', function (e) {
        e.preventDefault();
        const btn    = $(this);
        const form   = btn.closest('.js-delete-form');
        const entity = btn.data('entity') || 'este registro';

        Swal.fire({
            title: '¿Estás seguro?',
            text: `Se eliminará: ${entity}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-trash me-1"></i>Sí, eliminar',
            cancelButtonText: '<i class="ti ti-x me-1"></i>Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(function () {
        $('#satRetencionesTable').DataTable({
            responsive: false,
            processing: true,
            dom: '<"top"Bf>rt<"bottom"lip>',
            pageLength: 50,
            buttons: [
                {
                    text: '<i class="ti ti-plus me-1"></i> Nueva retención',
                    className: 'btn btn-primary btn-sm',
                    action: function () {
                        window.location.href = "{{ route('sat-retenciones.create') }}";
                    }
                },
                { extend: 'excel', text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'copy',  text: '<i class="ti ti-copy me-1"></i> Copy',              className: 'btn btn-warning btn-sm' },
                { extend: 'pdf',   text: '<i class="ti ti-file-text me-1"></i> PDF',           className: 'btn btn-info btn-sm', orientation: 'landscape', pageSize: 'A4' }
            ],
            ajax: {
                url: "{{ route('sat-retenciones.datatable') }}",
                type: 'GET',
                error: function (xhr) {
                    console.error('Error en DataTable:', xhr.responseText);
                }
            },
            columns: [
                { data: 'clave',                   name: 'clave',                   width: '90px' },
                { data: 'nombre',                  name: 'nombre' },
                { data: 'impuesto',                name: 'impuesto',                orderable: false, searchable: false, className: 'text-center' },
                { data: 'porcentaje_display',      name: 'porcentaje_display',      width: '160px' },
                { data: 'requiere_cfdi_retencion', name: 'requiere_cfdi_retencion', orderable: false, searchable: false, className: 'text-center' },
                { data: 'activo',                  name: 'activo',                  orderable: false, searchable: false, className: 'text-center' },
                { data: 'actions',                 name: 'actions',                 orderable: false, searchable: false, className: 'text-end' }
            ],
            language: { url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}" },
            drawCallback: function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });
    });
</script>
@endpush
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/sat_retenciones/index.blade.php
git commit -m "feat: vista index sat_retenciones con DataTable"
```

---

## Task 6: Vista — Create

**Files:**
- Create: `resources/views/sat_retenciones/create.blade.php`

- [ ] **Step 1: Crear el archivo**

```blade
@extends('layouts.zircos')

@section('title', 'Nueva Retención SAT')
@section('page.title', 'Nueva Retención SAT')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sat-retenciones.index') }}">Retenciones SAT</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
    <form action="{{ route('sat-retenciones.store') }}" method="POST" class="card">
        @csrf

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-receipt-tax me-1"></i> Nueva Retención SAT</h5>
        </div>

        <div class="card-body">
            @include('sat_retenciones.partials.form', ['sat_retencion' => $sat_retencion])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('sat-retenciones.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Guardar
            </button>
        </div>
    </form>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/sat_retenciones/create.blade.php
git commit -m "feat: vista create sat_retenciones"
```

---

## Task 7: Vista — Edit

**Files:**
- Create: `resources/views/sat_retenciones/edit.blade.php`

- [ ] **Step 1: Crear el archivo**

```blade
@extends('layouts.zircos')

@section('title', 'Editar Retención SAT')
@section('page.title', 'Editar Retención SAT')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sat-retenciones.index') }}">Retenciones SAT</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <form action="{{ route('sat-retenciones.update', $sat_retencion->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-header">
            <h5 class="mb-0">
                <i class="ti ti-receipt-tax me-1"></i>
                Editar: {{ $sat_retencion->clave }} — {{ $sat_retencion->nombre }}
            </h5>
        </div>

        <div class="card-body">
            @include('sat_retenciones.partials.form', ['sat_retencion' => $sat_retencion])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('sat-retenciones.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Actualizar
            </button>
        </div>
    </form>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/sat_retenciones/edit.blade.php
git commit -m "feat: vista edit sat_retenciones"
```

---

## Task 8: Sidebar

**Files:**
- Modify: `resources/views/layouts/partials/sidebar-staff.blade.php`

- [ ] **Step 1: Actualizar la variable `$openConfiguration`**

Localiza el bloque `@php` de `$openConfiguration` (alrededor de la línea 384):

```php
@php
$openConfiguration =
    request()->routeIs('companies.*') ||
    request()->routeIs('stations.*') ||
    request()->routeIs('departments.*') ||
    request()->routeIs('receiving-locations.*') ||
    request()->routeIs('taxes.*') ||
    request()->routeIs('approval-levels.*');
@endphp
```

Reemplázalo por:

```php
@php
$openConfiguration =
    request()->routeIs('companies.*') ||
    request()->routeIs('stations.*') ||
    request()->routeIs('departments.*') ||
    request()->routeIs('receiving-locations.*') ||
    request()->routeIs('taxes.*') ||
    request()->routeIs('approval-levels.*') ||
    request()->routeIs('sat-retenciones.*');
@endphp
```

- [ ] **Step 2: Agregar la entrada `<li>` dentro del accordion `#sidebarConfigurations`**

Localiza la entrada de "Niveles de Autorización" (alrededor de la línea 434):

```blade
            <li class="side-nav-item">
                <a href="{{ route('approval-levels.index') }}"
                    class="side-nav-link {{ request()->routeIs('approval-levels.*') ? 'active' : '' }}">
                    <span class="menu-text">Niveles de Autorización</span>
                    <span class="badge bg-soft-danger text-danger ms-auto">🛡️</span>
                </a>
            </li>
```

Añade inmediatamente **después** de ese bloque (antes del `</ul>` de cierre):

```blade
            <li class="side-nav-item">
                <a href="{{ route('sat-retenciones.index') }}"
                    class="side-nav-link {{ request()->routeIs('sat-retenciones.*') ? 'active' : '' }}">
                    <span class="menu-text">Retenciones SAT</span>
                </a>
            </li>
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/partials/sidebar-staff.blade.php
git commit -m "feat: entrada Retenciones SAT en sidebar CONFIGURACIÓN"
```

---

## Task 9: Verificación manual

- [ ] **Step 1: Limpiar caché de rutas y vistas**

```bash
php artisan route:clear && php artisan view:clear
```

- [ ] **Step 2: Navegar a `/sat-retenciones` como superadmin**

Verificar:
- La tabla DataTable carga y muestra las 14 retenciones del seeder.
- Las columnas Impuesto, CFDI Ret. y Activo muestran badges con colores correctos.
- El botón "Nueva retención" navega a `/sat-retenciones/create`.
- El botón editar (lápiz) navega a `/sat-retenciones/{id}/edit`.
- El botón eliminar (basura) dispara el SweetAlert con el texto `CLAVE — Nombre`.

- [ ] **Step 3: Probar Create**

- Enviar formulario vacío → verificar que aparecen los mensajes de validación en español.
- Llenar todos los campos y guardar → verificar redirect a index con alerta verde "Retención creada correctamente."
- Intentar crear con la misma clave → verificar mensaje "Ya existe una retención con esa clave."

- [ ] **Step 4: Probar Edit**

- Editar una retención existente → verificar que los campos se pre-populan correctamente.
- Guardar sin cambios → verificar redirect a index con alerta "Retención actualizada correctamente."
- Dejar `porcentaje` vacío → verificar que se guarda como `null` (tasa variable).

- [ ] **Step 5: Probar Delete**

- Click en basura → verificar que SweetAlert muestra la clave y nombre del registro.
- Confirmar → verificar que el registro desaparece de la tabla y aparece alerta "Retención eliminada correctamente."

- [ ] **Step 6: Probar acceso denegado**

Cambiar temporalmente a un usuario con rol `buyer` e intentar acceder a `/sat-retenciones`. Verificar respuesta `403 Forbidden`.

- [ ] **Step 7: Verificar que el sidebar abre el accordion correcto**

Al navegar a cualquier ruta `sat-retenciones.*`, el accordion "Catálogos" debe quedar abierto y la entrada "Retenciones SAT" debe tener la clase `active`.
