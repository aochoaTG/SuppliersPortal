@extends('layouts.zircos')

@section('title', 'Provisiones')
@section('page.title', 'Provisiones Financieras')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Provisiones por Recepción</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Recepción</th>
                        <th>Proveedor</th>
                        <th>Provisionado</th>
                        <th>Facturado</th>
                        <th>Diferencia</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($provisions as $provision)
                        <tr>
                            <td class="fw-semibold">{{ $provision->reception?->folio }}</td>
                            <td>{{ $provision->supplier?->company_name }}</td>
                            <td>${{ number_format((float) $provision->provision_amount, 2) }} {{ $provision->currency }}</td>
                            <td>{{ $provision->invoice_amount !== null ? '$' . number_format((float) $provision->invoice_amount, 2) : '-' }}</td>
                            <td>${{ number_format((float) $provision->difference_amount, 2) }}</td>
                            <td><span class="badge bg-{{ $provision->getStatusBadgeClass() }}">{{ $provision->getStatusLabel() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('financial-provisions.show', $provision) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Sin provisiones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $provisions->links() }}
    </div>
</div>
@endsection
