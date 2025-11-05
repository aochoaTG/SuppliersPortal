@extends('layouts.zircos')

@php
    $selectedCompanyId =
        $selectedCompanyId ?? old('company_id', $requisition->company_id ?? (auth()->user()->company_id ?? null));
@endphp

@section('title', 'Edición de Requisiciones')
@section('page.title', 'Edición de Requisiciones')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisiciones</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
    <form action="{{ route('requisitions.update', $requisition->id) }}" method="POST" class="card">
        @csrf
        @method('PUT')

        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-file-dollar"></i> Editar Requisición — {{ $requisition->folio }}</h5>
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
            <a href="{{ route('requisitions.show', $requisition) }}" class="btn btn-light">
                <i class="ti ti-arrow-left"></i> Regresar
            </a>

            <div class="d-flex gap-2">
                {{-- Guardar cambios --}}
                <button type="submit" name="action" value="save" class="btn btn-primary">
                    <i class="ti ti-device-floppy"></i> Guardar cambios
                </button>

                @if (in_array($requisition->status, ['draft', 'on_hold', 'in_review']))
                    {{-- Enviar a revisión --}}
                    <button type="submit" name="action" value="submit_review" class="btn btn-success">
                        <i class="ti ti-send"></i> Guardar y enviar a revisión
                    </button>
                @endif
            </div>
        </div>
    </form>
@endsection
