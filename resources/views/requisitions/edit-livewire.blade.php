@extends('layouts.zircos')

@section('title', 'Editar Requisición')

@section('page.title', 'Editar Requisición')

@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
<li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisiciones</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="container-fluid">
    {{-- Aquí se carga el componente Livewire --}}
    @livewire('requisition-form', ['requisition' => $requisition])
</div>
@endsection