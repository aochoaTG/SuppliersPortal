@extends('layouts.zircos')

@section('title', 'Gestión de Cotizaciones')

@section('page.title', 'Gestión de Cotizaciones')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Gestión de Cotizaciones</li>
@endsection

@section('content')
    @livewire('rfq.rfq-index')
@endsection