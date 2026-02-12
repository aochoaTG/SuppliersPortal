@extends('layouts.zircos')

@section('title', 'Editar Centro de Costo')
@section('page.title', 'Editar Centro de Costo')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('cost-centers.index') }}">Centros de Costo</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <form action="{{ route('cost-centers.update', $cost_center->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-hierarchy-2"></i> Editar: {{ $cost_center->name }}</h5>
        </div>

        <div class="card-body">
            @include('cost_centers.partials.form', [
                'costCenter' => $cost_center,
                'categories' => $categories,
                'companies' => $companies,
                'users' => $users,
            ])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('cost-centers.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Regresar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy"></i> Actualizar
            </button>
        </div>
    </form>
@endsection
