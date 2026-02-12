@extends('layouts.zircos')

@section('title', 'Nuevo Departamento')
@section('page.title', 'Nuevo Departamento')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departamentos</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-plus"></i> Crear departamento</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('departments.store') }}" method="POST" novalidate>
                @csrf
                @include('departments._form', ['department' => null])
            </form>
        </div>
    </div>
@endsection
