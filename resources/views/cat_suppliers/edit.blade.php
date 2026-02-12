@extends('layouts.zircos')

@section('title', 'Editar Proveedor')

@section('page.title', 'Editar Proveedor')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cat-suppliers.index') }}">Proveedores</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="container">
    <form method="POST" action="{{ route('cat-suppliers.update', $catSupplier) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" name="name" id="name"
                   value="{{ old('name', $catSupplier->name) }}"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="rfc" class="form-label">RFC</label>
            <input type="text" name="rfc" id="rfc"
                   value="{{ old('rfc', $catSupplier->rfc) }}"
                   class="form-control @error('rfc') is-invalid @enderror">
            @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email', $catSupplier->email) }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="bank" class="form-label">Banco</label>
            <input type="text" name="bank" id="bank"
                   value="{{ old('bank', $catSupplier->bank) }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label for="account_number" class="form-label">Nmero de cuenta</label>
            <input type="text" name="account_number" id="account_number"
                   value="{{ old('account_number', $catSupplier->account_number) }}"
                   class="form-control">
        </div>

        <div class="mb-3">
            <label for="clabe" class="form-label">CLABE</label>
            <input type="text" name="clabe" id="clabe"
                   value="{{ old('clabe', $catSupplier->clabe) }}"
                   class="form-control">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="active" id="active" class="form-check-input"
                   value="1" {{ old('active', $catSupplier->active) ? 'checked' : '' }}>
            <label class="form-check-label" for="active">Activo</label>
        </div>

        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="{{ route('cat-suppliers.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
