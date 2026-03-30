@extends('layouts.zircos')

@section('title', 'Editar Categoría')
@section('page.title', 'Editar Categoría')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categorías</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <form action="{{ route('categories.update', $category->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-category me-1"></i> Editar: {{ $category->name }}</h5>
        </div>

        <div class="card-body">
            @include('categories.partials.form', ['category' => $category])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Volver
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Actualizar
            </button>
        </div>
    </form>
@endsection
