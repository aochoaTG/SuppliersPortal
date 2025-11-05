{{-- resources/views/stations/partials/form.blade.php --}}
<div class="modal-header">
    <h5 class="modal-title">
        <i class="ti ti-gas-station"></i>
        {{ $station->exists ? 'Editar estación' : 'Nueva estación' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form id="stationForm" action="{{ $action }}" method="{{ $method }}">
    @csrf
    @if (in_array($method, ['PUT', 'PATCH', 'DELETE']))
        @method($method)
    @endif

    <div class="modal-body">
        <div id="formErrors" class="d-none"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre de estación *</label>
                <input type="text" name="station_name" value="{{ old('station_name', $station->station_name) }}"
                    class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Empresa</label>
                <select name="company_id" class="select2 form-select" @disabled($station->exists && !is_null($station->company_id))>
                    <option value="">—</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}" @selected(old('company_id', $station->company_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @if ($station->exists && !is_null($station->company_id))
                    <small class="text-danger d-block">Intransferible: no se puede cambiar la empresa.</small>
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $station->email) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">IP Servidor</label>
                <input type="text" name="server_ip" value="{{ old('server_ip', $station->server_ip) }}"
                    class="form-control" placeholder="192.168.0.10">
            </div>
            <div class="col-md-4">
                <label class="form-label">Base de datos</label>
                <input type="text" name="database_name" value="{{ old('database_name', $station->database_name) }}"
                    class="form-control" placeholder="SG12_11007">
            </div>

            <div class="col-md-3">
                <label class="form-label">País</label>
                <input type="text" name="country" value="{{ old('country', $station->country) }}"
                    class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <input type="text" name="state" value="{{ old('state', $station->state) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Municipio</label>
                <input type="text" name="municipality" value="{{ old('municipality', $station->municipality) }}"
                    class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Lugar de expedición</label>
                <input type="text" name="expedition_place"
                    value="{{ old('expedition_place', $station->expedition_place) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Dirección</label>
                <input type="text" name="address" value="{{ old('address', $station->address) }}"
                    class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Permiso CRE</label>
                <input type="text" name="cre_permit" value="{{ old('cre_permit', $station->cre_permit) }}"
                    class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Sistema externo</label>
                <select name="source_system" class="form-select">
                    <option value="">—</option>
                    @foreach (['ControlGas', 'SG12', 'CRE', 'TRESS', 'Other'] as $sys)
                        <option value="{{ $sys }}" @selected(old('source_system', $station->source_system) === $sys)>{{ $sys }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">External ID</label>
                <input type="text" name="external_id" value="{{ old('external_id', $station->external_id) }}"
                    class="form-control" placeholder="E04188">
                <small class="text-muted">El par (sistema, ID) debe ser único.</small>
            </div>

            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="is_active" class="form-select">
                    <option value="1" @selected(old('is_active', $station->is_active ?? 1) == 1)>Activa</option>
                    <option value="0" @selected(old('is_active', $station->is_active ?? 1) == 0)>Inactiva</option>
                </select>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> Guardar
        </button>
    </div>
</form>
