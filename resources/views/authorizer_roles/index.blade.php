@extends('layouts.zircos')

@section('title', 'Roles Autorizadores')
@section('page.title', 'Roles Autorizadores')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Roles autorizadores</li>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Matriz de Facultades</h5>
                <small class="text-muted">Fuente inicial: hoja `15 abr 26`, fila `Autorizacion + IVA`.</small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Rol autorizador</th>
                        <th>Límite con IVA</th>
                        <th>Fuente</th>
                        <th>Estatus</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>
                                @if($role->approval_limit !== null)
                                    ${{ number_format((float) $role->approval_limit, 2) }}
                                @else
                                    <span class="text-muted">Sin facultad de autorización</span>
                                @endif
                            </td>
                            <td>{{ $role->matrix_sheet }} · {{ $role->matrix_reference }}</td>
                            <td>
                                <span class="badge {{ $role->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $role->is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('authorizer-roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
