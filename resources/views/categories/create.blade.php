@extends('layouts.zircos')

@section('title', 'Crear Categoria')
@section('page.title', 'Crear Categoria')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categorias</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
    <form action="{{ route('categories.store') }}" method="POST" class="card">
        @csrf

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-category"></i> Nueva Categoria</h5>
        </div>

        <div class="card-body">
            @include('categories.partials.form', ['category' => $category])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('categories.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Guardar
            </button>
        </div>
    </form>
@endsection
