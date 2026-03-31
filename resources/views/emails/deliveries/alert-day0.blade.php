{{-- Alerta Día 0: Entrega registrada por proveedor --}}
<p>Estimado equipo,</p>

<p>El proveedor <strong>{{ $supplierName }}</strong> ha registrado una entrega contra la siguiente orden de compra:</p>

<table style="border-collapse:collapse;width:100%;max-width:500px;margin:15px 0;">
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Folio OC</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $order->folio }}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Proveedor</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $supplierName }}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Fecha de entrega</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $deliveryDate }}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Punto de entrega</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $receivingLocationName }}</td>
    </tr>
</table>

<p><strong>Debe registrar la recepción en el sistema en los próximos 3 días hábiles.</strong></p>

@if($evidenceUrl)
<p>
    <a href="{{ $evidenceUrl }}" style="display:inline-block;padding:10px 20px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:5px;">
        Ver remisión adjunta
    </a>
</p>
@endif

<p style="color:#dc3545;font-weight:bold;">
    Si no se registra la recepción dentro del plazo, se escalarán alertas a Finanzas y Dirección.
</p>

<p>Atentamente,<br>Sistema Portal de Proveedores — TotalGas</p>
