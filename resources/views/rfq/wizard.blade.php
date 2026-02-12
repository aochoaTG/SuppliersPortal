@extends('layouts.zircos')

@section('title', 'Wizard de Cotización')

@section('page.title', 'Wizard de Cotización')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rfq.index') }}">Gestión de Cotizaciones</a></li>
    <li class="breadcrumb-item active">Wizard de Cotización</li>
@endsection

@section('content')
    @livewire('rfq.quotation-wizard', ['requisition' => $requisition])
@endsection