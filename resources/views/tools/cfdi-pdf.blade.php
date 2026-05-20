<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: #333; }

        .watermark {
            position: fixed;
            top: 42%;
            left: 5%;
            width: 90%;
            text-align: center;
            font-size: 48px;
            font-weight: bold;
            color: rgba(220, 0, 0, 0.10);
            transform: rotate(-35deg);
            white-space: nowrap;
            z-index: 0;
        }

        .content { position: relative; z-index: 1; padding: 15px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background-color: #2c3e50; color: #fff; padding: 5px 6px; text-align: left; font-size: 8px; }
        td { padding: 4px 6px; border: 1px solid #ddd; vertical-align: top; }
        .label { font-weight: bold; color: #555; width: 30%; background: #f8f8f8; }

        .header-box { border: 2px solid #2c3e50; padding: 10px; margin-bottom: 8px; }
        .header-title { font-size: 20px; font-weight: bold; color: #2c3e50; text-align: right; }
        .header-sub { font-size: 11px; color: #666; text-align: right; }
        .emisor-name { font-size: 13px; font-weight: bold; }

        .totals-table td { text-align: right; }
        .totals-table .label { text-align: left; }
        .total-row td { font-weight: bold; font-size: 11px; background: #2c3e50; color: #fff; }

        .uuid-box { background: #f0f0f0; border: 1px solid #ccc; padding: 6px; font-size: 8px; word-break: break-all; margin-bottom: 8px; }
        .badge-prueba { background: #dc3545; color: #fff; text-align: center; padding: 4px; font-size: 10px; font-weight: bold; margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="watermark">DOCUMENTO DE PRUEBA — NO VÁLIDO FISCALMENTE</div>

    <div class="content">
        <div class="badge-prueba">DOCUMENTO DE PRUEBA — NO VÁLIDO FISCALMENTE</div>

        {{-- Encabezado Emisor --}}
        <div class="header-box">
            <table style="border:none; margin:0;">
                <tr>
                    <td style="border:none; width:60%; padding:0;">
                        <div class="emisor-name">{{ $nombreEmisor }}</div>
                        <div>RFC: <strong>{{ $rfcEmisor }}</strong></div>
                        <div>Régimen: {{ $regimenFiscal }}</div>
                    </td>
                    <td style="border:none; text-align:right; padding:0;">
                        <div class="header-title">FACTURA</div>
                        <div class="header-sub">Serie <strong>{{ $serie ?: 'N/A' }}</strong> &nbsp; Folio <strong>{{ $folio ?: 'N/A' }}</strong></div>
                        <div class="header-sub">Fecha: <strong>{{ $fecha }}</strong></div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Receptor --}}
        <table>
            <tr><th colspan="4">DATOS DEL RECEPTOR</th></tr>
            <tr>
                <td class="label">RFC</td><td>{{ $rfcReceptor }}</td>
                <td class="label">Nombre</td><td>{{ $nombreReceptor }}</td>
            </tr>
            <tr>
                <td class="label">Código Postal</td><td>{{ $domicilioFiscalReceptor }}</td>
                <td class="label">Régimen</td><td>{{ $regimenFiscalReceptor }}</td>
            </tr>
            <tr>
                <td class="label">Uso CFDI</td><td colspan="3">{{ $usoCFDI }}</td>
            </tr>
        </table>

        {{-- Datos del comprobante --}}
        <table>
            <tr><th colspan="6">DATOS DEL COMPROBANTE</th></tr>
            <tr>
                <td class="label">Forma de pago</td><td>{{ $formaPago }}</td>
                <td class="label">Método de pago</td><td>{{ $metodoPago }}</td>
                <td class="label">Moneda</td><td>{{ $moneda }}</td>
            </tr>
        </table>

        {{-- Conceptos --}}
        <table>
            <tr>
                <th>Clave Prod/Serv</th>
                <th>Clave Unidad</th>
                <th>Cantidad</th>
                <th>Descripción</th>
                <th style="text-align:right;">Val. Unitario</th>
                <th style="text-align:right;">Importe</th>
            </tr>
            <tr>
                <td>{{ $claveProdServ }}</td>
                <td>{{ $claveUnidad }}</td>
                <td>{{ number_format((float)$cantidad, 2) }}</td>
                <td>{{ $descripcion }}</td>
                <td style="text-align:right;">${{ number_format((float)$valorUnitario, 2) }}</td>
                <td style="text-align:right;">${{ number_format((float)$subtotal, 2) }}</td>
            </tr>
        </table>

        {{-- Totales --}}
        <table class="totals-table" style="width: 50%; margin-left: 50%;">
            <tr>
                <td class="label">Subtotal</td>
                <td>${{ number_format((float)$subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">IVA ({{ number_format((float)$tasaIVA * 100, 4) }}%)</td>
                <td>${{ number_format((float)$iva, 2) }}</td>
            </tr>
            @foreach($retencionesData as $ret)
            <tr>
                <td class="label">Ret. {{ $ret['clave'] }} ({{ number_format($ret['tasa'] * 100, 4) }}%)</td>
                <td>-${{ number_format((float)$ret['importe'], 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td class="label" style="color:#fff; background:#2c3e50;">TOTAL</td>
                <td style="background:#2c3e50;">${{ number_format((float)$total, 2) }} {{ $moneda }}</td>
            </tr>
        </table>

        {{-- UUID / Timbre --}}
        <div class="uuid-box">
            <strong>Folio Fiscal (UUID):</strong> {{ $uuid }}<br>
            <strong>Fecha Timbrado:</strong> {{ $fecha }}<br>
            <strong>RFC PAC (mock):</strong> SAT970701NN3 &nbsp;&nbsp;
            <strong>No. Certificado SAT:</strong> 00000000000000000000
        </div>
    </div>
</body>
</html>
