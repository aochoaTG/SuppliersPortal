@extends('layouts.zircos')

@section('title', 'Nueva Ubicación de Recepción')
@section('page.title', 'Nueva Ubicación de Recepción')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('receiving-locations.index') }}">Ubicaciones de Recepción</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-plus"></i> Nueva Ubicación de Recepción</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('receiving-locations.store') }}" method="POST" novalidate>
                @csrf
                @include('receiving-locations._form', ['location' => $location])
            </form>
        </div>
    </div>
@endsection
