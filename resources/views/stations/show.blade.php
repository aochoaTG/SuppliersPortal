@extends('layouts.zircos')

@section('title', $station->station_name)

@section('page.title', $station->station_name)

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stations.index') }}">Estaciones</a></li>
    <li class="breadcrumb-item active">{{ $station->station_name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-0"><i class="ti ti-gas-station"></i> {{ $station->station_name }}</h4>
                            <small class="text-muted">ID: {{ $station->id }}</small>
                        </div>
                        <div>
                            <span class="badge {{ $station->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $station->is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div><strong>Empresa:</strong>
                                @if ($station->company)
                                    <span class="badge bg-primary">{{ $station->company->name }}</span>
                                @else
                                    <span class="badge badge-null">Sin empresa</span>
                                @endif
                            </div>
                            <div><strong>Dirección:</strong> {{ $station->address }}</div>
                            <div><strong>Lugar de expedición:</strong> {{ $station->expedition_place }}</div>
                            <div><strong>Estado/Municipio:</strong> {{ $station->state }} / {{ $station->municipality }}
                            </div>
                            <div><strong>País:</strong> {{ $station->country }}</div>
                        </div>
                        <div class="col-md-6">
                            <div><strong>IP Servidor:</strong> {{ $station->server_ip }}</div>
                            <div><strong>Base de datos:</strong> {{ $station->database_name }}</div>
                            <div><strong>Permiso CRE:</strong> {{ $station->cre_permit }}</div>
                            <div><strong>Sistema externo / ID:</strong> {{ $station->source_system ?? '—' }} @if ($station->external_id)
                                    · {{ $station->external_id }}
                                @endif
                            </div>
                            <div><strong>Email:</strong> {{ $station->email }}</div>
                        </div>
                    </div>

                    <div class="d-flex mt-3 gap-2">
                        <a href="{{ route('stations.edit', $station) }}" class="btn btn-outline-primary"><i
                                class="ti ti-edit"></i> Editar</a>
                        <form method="POST" action="{{ route('stations.toggle-active', $station) }}">
                            @csrf
                            <button class="btn btn-outline-secondary">
                                <i class="ti {{ $station->is_active ? 'ti-toggle-left' : 'ti-toggle-right' }}"></i>
                                {{ $station->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                        <a href="{{ route('stations.index') }}" class="btn btn-light">Volver</a>
                    </div>
                </div>
            </div>

            {{-- Si no tiene empresa, ofrecer vincular desde aquí también --}}
            @if (!$station->company)
                <div class="card">
                    <div class="card-body">
                        <h5><i class="ti ti-link"></i> Vincular a empresa</h5>
                        <form method="POST" action="{{ route('stations.link-company', $station) }}" class="row g-2">
                            @csrf
                            <div class="col-md-6">
                                <select name="company_id" class="select2 form-select" required>
                                    <option value="">Seleccione empresa…</option>
                                    @foreach (\App\Models\Company::orderBy('name')->get(['id', 'name']) as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Una vez vinculada, la estación no podrá transferirse.</small>
                            </div>
                            <div class="col-md-6">
                                <button class="btn btn-primary"><i class="ti ti-check"></i> Vincular</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5><i class="ti ti-info-circle"></i> Metadatos</h5>
                    <div><strong>Creación:</strong> {{ $station->created_at }}</div>
                    <div><strong>Actualización:</strong> {{ $station->updated_at }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>
@endpush
