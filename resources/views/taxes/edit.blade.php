@extends('layouts.zircos')

@section('title', 'Editar Impuesto')

@section('page.title', 'Editar Impuesto')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="javascript:void(0);">Inicio</a></li>
    <li class="breadcrumb-item"><a href="javascript:void(0);">Administración</a></li>
    <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Impuestos</a></li>
    <li class="breadcrumb-item active">{{ $tax->name }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Editar: {{ $tax->name }}</h5>
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

        <form action="{{ route('taxes.update', $tax) }}" method="POST" novalidate>
            @method('PUT')
            @include('taxes.partials.form', ['tax' => $tax])
        </form>
    </div>
</div>
@endsection
