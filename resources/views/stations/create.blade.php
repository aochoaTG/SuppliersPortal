@extends('layouts.zircos')

@section('title', 'Nueva Estación')

@section('page.title', 'Nueva Estación')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stations.index') }}">Estaciones</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <h4 class="header-title"><i class="ti ti-gas-station"></i> Nueva estación</h4>
            <form method="POST" action="{{ route('stations.store') }}" class="row g-3 mt-2">
                @csrf

                <div class="col-md-4">
                    <label class="form-label">Nombre de estación *</label>
                    <input type="text" name="station_name" value="{{ old('station_name') }}" class="form-control"
                        required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Empresa (opcional)</label>
                    <select name="company_id" class="select2 form-select">
                        <option value="">—</option>
                        @foreach (\App\Models\Company::orderBy('name')->get(['id', 'name']) as $c)
                            <option value="{{ $c->id }}" @selected(old('company_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Si la asignas, no podrás transferirla después.</small>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">País</label>
                    <input type="text" name="country" value="{{ old('country') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <input type="text" name="state" value="{{ old('state') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Municipio</label>
                    <input type="text" name="municipality" value="{{ old('municipality') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Lugar de expedición</label>
                    <input type="text" name="expedition_place" value="{{ old('expedition_place') }}"
                        class="form-control">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IP del servidor</label>
                    <input type="text" name="server_ip" value="{{ old('server_ip') }}" class="form-control"
                        placeholder="192.168.0.10">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Base de datos</label>
                    <input type="text" name="database_name" value="{{ old('database_name') }}" class="form-control"
                        placeholder="SG12_11007">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Permiso CRE</label>
                    <input type="text" name="cre_permit" value="{{ old('cre_permit') }}" class="form-control"
                        placeholder="PL/XXXXX/ES/AAAA">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sistema externo / ID</label>
                    <div class="input-group">
                        <select name="source_system" class="form-select">
                            <option value="">—</option>
                            @foreach (['ControlGas', 'SG12', 'CRE', 'TRESS', 'Other'] as $sys)
                                <option value="{{ $sys }}" @selected(old('source_system') === $sys)>{{ $sys }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="external_id" value="{{ old('external_id') }}" class="form-control"
                            placeholder="E04188">
                    </div>
                    <small class="text-muted">El par (sistema, ID) debe ser único.</small>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('stations.index') }}" class="btn btn-light">Cancelar</a>
                    <button class="btn btn-primary"><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
            </form>
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
