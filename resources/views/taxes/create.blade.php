@extends('layouts.zircos')

@section('title', 'Nuevo Impuesto')

@section('page.title', 'Nuevo Impuesto')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item">Administración</li>
    <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Impuestos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="ti ti-receipt-tax me-1"></i> Crear impuesto</h5>
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('taxes.store') }}" method="POST" novalidate>
            @include('taxes.partials.form', ['tax' => $tax])
        </form>
    </div>
</div>
@endsection
