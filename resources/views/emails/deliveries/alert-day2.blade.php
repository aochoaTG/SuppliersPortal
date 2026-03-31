{{-- Alerta Día 2: Recordatorio urgente — vence mañana --}}
<p>Estimado equipo,</p>

<p style="color:#dc3545;font-size:16px;font-weight:bold;">
    ⏰ MAÑANA VENCE el plazo para registrar la recepción de la siguiente orden de compra:
</p>

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
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Fecha de entrega reportada</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $deliveryDate }}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Punto de entrega</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $receivingLocationName }}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#fff3cd;font-weight:bold;">Fecha límite</td>
        <td style="padding:8px;border:1px solid #ddd;background:#fff3cd;font-weight:bold;">
            {{ $order->reception_deadline_at->format('d/m/Y') }}
        </td>
    </tr>
</table>

<p><strong>Acción requerida:</strong> Registrar la recepción en el sistema o reportar incidencia.</p>

<p style="color:#856404;background:#fff3cd;padding:10px;border-radius:5px;">
    Si el plazo vence sin captura, se notificará automáticamente a la Dirección de Administración y Finanzas.
</p>

<p>Atentamente,<br>Sistema Portal de Proveedores — TotalGas</p>
