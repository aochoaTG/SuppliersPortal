@extends('layouts.zircos')

@section('title', 'Requisiciones rechazadas')

@section('page.title', 'Requisiciones rechazadas')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('requisitions.index') }}">Requisiciones</a></li>
    <li class="breadcrumb-item active">Rechazadas</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-circle-x"></i> Rechazadas</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Compañía</th>
                        <th>Centro</th>
                        <th>Revisor</th>
                        <th>Rechazado por</th>
                        <th>Motivo</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->folio }}</td>
                            <td>{{ $r->company->name ?? '—' }}</td>
                            <td>{{ ($r->costCenter->code ? '[' . $r->costCenter->code . '] ' : '') . ($r->costCenter->name ?? '—') }}
                            </td>
                            <td>{{ $r->reviewer->name ?? '—' }}</td>
                            <td>{{ $r->rejecter->name ?? '—' }}</td>
                            <td>{{ $r->rejection_reason ?? '—' }}</td>
                            <td>${{ number_format($r->amount_requested, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center">Sin rechazadas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $rows->links() }}
        </div>
    </div>
@endsection
