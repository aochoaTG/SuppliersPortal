@extends('layouts.zircos')

@section('title', 'Crear Requisición')
@section('page.title', 'Crear Requisición')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisiciones</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@section('content')
    <form action="{{ route('requisitions.store') }}" method="POST" class="card">
        {{-- @include('requisitions._budget_preview', ['requisition' => $requisition ?? null]) --}}
        @csrf

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-file-dollar"></i> Nueva Requisición</h5>
        </div>

        <div class="card-body">
            @include('requisitions._form', [
                'requisition' => $requisition,
                'costCenters' => $costCenters,
                'currencies' => $currencies,
                'statusOpts' => $statusOpts,
            ])
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('requisitions.index') }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Regresar
            </a>

            <div class="d-flex gap-2">
                {{-- A) Guardar como borrador --}}
                <button type="submit" name="submit_action" value="draft" class="btn btn-outline-secondary">
                    <i class="ti ti-device-floppy"></i> Guardar borrador
                </button>

                {{-- B) Guardar y enviar a revisión --}}
                <button type="submit" name="submit_action" value="to_review" class="btn btn-primary">
                    <i class="ti ti-send"></i> Guardar y enviar a revisión
                </button>
            </div>
        </div>

    </form>
@endsection
