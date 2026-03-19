@extends('layouts.zircos')

@section('title', 'Editar Ubicación de Recepción')
@section('page.title', 'Editar Ubicación de Recepción')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('receiving-locations.index') }}">Ubicaciones de Recepción</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-edit"></i> Editar Ubicación: {{ $location->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('receiving-locations.update', $location) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('receiving-locations._form', ['location' => $location])
            </form>
        </div>
    </div>
@endsection
