@extends('layouts.zircos')

@section('title', 'Editar Departamento')
@section('page.title', 'Editar Departamento')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departamentos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-edit"></i> Editar Departamento</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('departments.update', $department) }}" method="POST" novalidate>
                @csrf
                @method('PUT')
                @include('departments._form', ['department' => $department])
            </form>
        </div>
    </div>
@endsection
