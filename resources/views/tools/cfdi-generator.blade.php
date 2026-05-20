@extends('layouts.zircos')

@section('title', 'Generador CFDI de Prueba')
@section('page.title', 'Herramientas — Generador CFDI de Prueba')

@section('content')
<div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
    <i class="ti ti-alert-triangle me-2 fs-5"></i>
    <div><strong>Solo para pruebas.</strong> Los archivos generados no son válidos fiscalmente y no se guardan en el sistema.</div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form id="cfdi-form" method="POST" action="">
    @csrf

    {{-- ── BLOQUE 1: EMISOR ─────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">1. Emisor (Proveedor)</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">RFC Emisor <span class="text-danger">*</span></label>
                    <select name="rfcEmisor" id="rfcEmisor" class="form-select" required>
                        <option value="">Seleccionar proveedor...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->rfc }}" data-nombre="{{ $s->company_name }}">
                                {{ $s->rfc }} — {{ $s->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
                    <input type="text" name="nombreEmisor" id="nombreEmisor" class="form-control" required maxlength="300" value="{{ old('nombreEmisor') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Régimen Fiscal <span class="text-danger">*</span></label>
                    <select name="regimenFiscal" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($regimenes as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('regimenFiscal') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 2: RECEPTOR ───────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">2. Receptor (Empresa)</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">RFC Receptor <span class="text-danger">*</span></label>
                    <select name="rfcReceptor" id="rfcReceptor" class="form-select" required>
                        <option value="">Seleccionar empresa...</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->rfc }}" data-nombre="{{ $c->name }}">
                                {{ $c->rfc }} — {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nombre / Razón Social <span class="text-danger">*</span></label>
                    <input type="text" name="nombreReceptor" id="nombreReceptor" class="form-control" required maxlength="300" value="{{ old('nombreReceptor') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                    <input type="text" name="domicilioFiscalReceptor" class="form-control" required maxlength="5" pattern="\d{5}" value="{{ old('domicilioFiscalReceptor') }}" placeholder="64000">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Régimen Fiscal Receptor <span class="text-danger">*</span></label>
                    <select name="regimenFiscalReceptor" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($regimenes as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('regimenFiscalReceptor') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Uso CFDI <span class="text-danger">*</span></label>
                    <select name="usoCFDI" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($usosCfdi as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('usoCFDI') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 3: COMPROBANTE ────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">3. Datos del Comprobante</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Serie</label>
                    <input type="text" name="serie" class="form-control" maxlength="25" value="{{ old('serie', 'A') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Folio</label>
                    <input type="text" name="folio" class="form-control" maxlength="40" value="{{ old('folio', '1') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="fecha" class="form-control" required value="{{ old('fecha', now()->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Forma de Pago <span class="text-danger">*</span></label>
                    <select name="formaPago" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        @foreach($formasPago as $clave => $desc)
                            <option value="{{ $clave }}" {{ old('formaPago', '03') == $clave ? 'selected' : '' }}>{{ $desc }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                    <select name="metodoPago" class="form-select" required>
                        <option value="PUE" {{ old('metodoPago', 'PUE') == 'PUE' ? 'selected' : '' }}>PUE - Pago en una sola exhibición</option>
                        <option value="PPD" {{ old('metodoPago') == 'PPD' ? 'selected' : '' }}>PPD - Pago en parcialidades o diferido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Moneda <span class="text-danger">*</span></label>
                    <select name="moneda" class="form-select" required>
                        <option value="MXN" {{ old('moneda', 'MXN') == 'MXN' ? 'selected' : '' }}>MXN</option>
                        <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BLOQUE 4: CONCEPTO, IMPUESTOS Y RETENCIONES ─────────────── --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">4. Concepto, Traslados y Retenciones</h6></div>
        <div class="card-body">

            <div class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Clave Prod/Serv <span class="text-danger">*</span></label>
                    <input type="text" name="claveProdServ" class="form-control" required maxlength="8" value="{{ old('claveProdServ', '84111506') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Clave Unidad <span class="text-danger">*</span></label>
                    <input type="text" name="claveUnidad" class="form-control" required maxlength="3" value="{{ old('claveUnidad', 'E48') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <input type="text" name="descripcion" class="form-control" required maxlength="1000" value="{{ old('descripcion', 'Servicios profesionales') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" required min="0.001" step="0.001" value="{{ old('cantidad', 1) }}" oninput="recalculate()">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valor Unitario <span class="text-danger">*</span></label>
                    <input type="number" name="valorUnitario" id="valorUnitario" class="form-control" required min="0" step="0.01" value="{{ old('valorUnitario', 0) }}" oninput="recalculate()">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Tasa IVA <span class="text-danger">*</span></label>
                    <select name="tasaIVA" id="tasaIVA" class="form-select" required onchange="recalculate()">
                        <option value="0.16" {{ old('tasaIVA', '0.16') == '0.16' ? 'selected' : '' }}>16%</option>
                        <option value="0.08" {{ old('tasaIVA') == '0.08' ? 'selected' : '' }}>8%</option>
                        <option value="0"    {{ old('tasaIVA') == '0'    ? 'selected' : '' }}>0%</option>
                    </select>
                </div>
            </div>

            <h6 class="mb-2">Retenciones</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Clave</th>
                            <th>Nombre</th>
                            <th>Impuesto SAT</th>
                            <th style="width:150px;">Porcentaje (%)</th>
                            <th style="width:110px;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retencionCatalog as $clave => $ret)
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox"
                                    class="form-check-input ret-checkbox"
                                    name="ret_enabled[]"
                                    id="ret_{{ $clave }}"
                                    value="{{ $clave }}"
                                    data-impuesto="{{ $ret['impuesto'] }}"
                                    onchange="toggleRetencion('{{ $clave }}')">
                                <input type="hidden" name="ret_impuesto[{{ $clave }}]" value="{{ $ret['impuesto'] }}">
                            </td>
                            <td class="align-middle">
                                <label for="ret_{{ $clave }}" class="mb-0 fw-bold">{{ $clave }}</label>
                            </td>
                            <td class="align-middle">
                                {{ $ret['nombre'] }}
                                @if($ret['no_cfdi'])
                                    <span class="badge bg-warning text-dark ms-1" title="No genera CFDI de Retenciones según el SAT">No CFDI</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $ret['impuesto'] === '001' ? 'ISR (001)' : 'IVA (002)' }}</td>
                            <td>
                                <input type="number"
                                    name="ret_tasa[{{ $clave }}]"
                                    id="ret_tasa_{{ $clave }}"
                                    class="form-control form-control-sm"
                                    value="{{ number_format($ret['tasa'] * 100, 4) }}"
                                    min="0" max="100" step="0.0001"
                                    {{ $ret['variable'] ? '' : 'readonly' }}
                                    disabled
                                    oninput="recalculate()">
                            </td>
                            <td class="align-middle text-end text-muted" id="ret_importe_{{ $clave }}">$0.00</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totales --}}
            <div class="row g-3 justify-content-end">
                <div class="col-md-4">
                    <table class="table table-sm table-bordered mb-0">
                        <tr>
                            <th class="bg-light">Subtotal</th>
                            <td><input type="number" name="subtotal" id="subtotal" class="form-control form-control-sm" step="0.01" required value="{{ old('subtotal', 0) }}" readonly></td>
                        </tr>
                        <tr>
                            <th class="bg-light">IVA</th>
                            <td><input type="number" name="iva" id="iva" class="form-control form-control-sm" step="0.01" required value="{{ old('iva', 0) }}" readonly></td>
                        </tr>
                        <tr id="ret-summary-row" style="display:none;">
                            <th class="bg-light">Total Retenciones</th>
                            <td class="text-end"><span id="total-retenciones-display">$0.00</span></td>
                        </tr>
                        <tr class="table-dark">
                            <th>TOTAL</th>
                            <td><input type="number" name="total" id="total" class="form-control form-control-sm" step="0.01" required value="{{ old('total', 0) }}" readonly></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── BOTONES ──────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <button type="button" class="btn btn-outline-primary" onclick="submitCfdi('{{ route('tools.cfdi.xml') }}')">
            <i class="ti ti-file-code me-1"></i> Descargar XML
        </button>
        <button type="button" class="btn btn-danger" onclick="submitCfdi('{{ route('tools.cfdi.pdf') }}')">
            <i class="ti ti-file-text me-1"></i> Descargar PDF
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const suppliersMap = @json($suppliers->keyBy('rfc')->map(fn($s) => $s->company_name));
    const companiesMap = @json($companies->keyBy('rfc')->map(fn($c) => $c->name));

    document.getElementById('rfcEmisor').addEventListener('change', function () {
        document.getElementById('nombreEmisor').value = suppliersMap[this.value] ?? '';
    });

    document.getElementById('rfcReceptor').addEventListener('change', function () {
        document.getElementById('nombreReceptor').value = companiesMap[this.value] ?? '';
    });

    window.toggleRetencion = function (clave) {
        const cb   = document.getElementById('ret_' + clave);
        const tasa = document.getElementById('ret_tasa_' + clave);
        tasa.disabled = !cb.checked;
        recalculate();
    };

    window.recalculate = function () {
        const cantidad      = parseFloat(document.getElementById('cantidad').value)      || 0;
        const valorUnitario = parseFloat(document.getElementById('valorUnitario').value) || 0;
        const tasaIVA       = parseFloat(document.getElementById('tasaIVA').value)       || 0;
        const subtotal      = cantidad * valorUnitario;
        const iva           = subtotal * tasaIVA;

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('iva').value      = iva.toFixed(2);

        let totalRet = 0;
        document.querySelectorAll('.ret-checkbox').forEach(function (cb) {
            const key     = cb.value;
            const tasaInp = document.getElementById('ret_tasa_' + key);
            const impEl   = document.getElementById('ret_importe_' + key);
            if (cb.checked) {
                const tasa    = (parseFloat(tasaInp.value) || 0) / 100;
                const importe = subtotal * tasa;
                totalRet     += importe;
                impEl.textContent = '$' + importe.toFixed(2);
            } else {
                impEl.textContent = '$0.00';
            }
        });

        document.getElementById('total').value = (subtotal + iva - totalRet).toFixed(2);
        document.getElementById('total-retenciones-display').textContent = '$' + totalRet.toFixed(2);
        document.getElementById('ret-summary-row').style.display = totalRet > 0 ? '' : 'none';
    };

    window.submitCfdi = function (action) {
        // Convert ret_tasa from % to decimal (0–1) before submitting
        document.querySelectorAll('[name^="ret_tasa["]').forEach(function (inp) {
            if (!inp.disabled) {
                inp.value = (parseFloat(inp.value) / 100).toFixed(6);
            }
        });
        const form = document.getElementById('cfdi-form');
        form.action = action;
        form.submit();
    };

    recalculate();
})();
</script>
@endpush
