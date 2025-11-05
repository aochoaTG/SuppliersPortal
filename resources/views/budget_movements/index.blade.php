@extends('layouts.zircos')

@section('title', 'Budget Movements')
@section('page.title', 'Budget Movements (Audit Log)')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-activity"></i> Budget Movements</h5>
        </div>

        <div class="card-body">
            <div class="timeline">
                @foreach ($movements as $m)
                    <div class="timeline-item mb-3">
                        <div>
                            <strong>{{ $m->moved_at->format('d/m/Y H:i') }}</strong>
                            <span class="badge bg-secondary">{{ $m->type }}</span>
                            <span class="fw-bold text-primary float-end">${{ number_format($m->amount, 2) }}</span>
                        </div>
                        @if ($m->note)
                            <div class="text-muted small">{{ $m->note }}</div>
                        @endif
                        @if ($m->requisition)
                            <div class="small">Requisition: {{ $m->requisition->id }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{ $movements->links() }}
        </div>
    </div>
@endsection
