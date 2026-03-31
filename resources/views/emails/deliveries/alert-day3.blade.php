{{-- Alerta Día 3: Alerta crítica — venció el plazo --}}
<p>Estimado equipo directivo,</p>

<p style="color:#dc3545;font-size:18px;font-weight:bold;">
    ⚠️ ALERTA CRÍTICA: Venció el plazo de captura de recepción
</p>

<p>Ha vencido el plazo de 3 días hábiles para registrar la recepción de la siguiente orden de compra sin que la estación haya capturado la recepción en el sistema:</p>

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
        <td style="padding:8px;border:1px solid #ddd;background:#f8f9fa;font-weight:bold;">Estación / Ubicación</td>
        <td style="padding:8px;border:1px solid #ddd;">{{ $receivingLocationName }}</td>
    </tr>
    <tr>
        <td style="padding:8px;border:1px solid #ddd;background:#f8d7da;font-weight:bold;">Días transcurridos</td>
        <td style="padding:8px;border:1px solid #ddd;background:#f8d7da;font-weight:bold;color:#dc3545;">3 días hábiles</td>
    </tr>
</table>

<p style="color:#dc3545;font-weight:bold;font-size:15px;">
    Acción requerida inmediata: Investigar y registrar recepción o reportar no conformidad.
</p>

<p style="background:#f8d7da;padding:10px;border-radius:5px;color:#721c24;">
    Mientras esta situación persista, el portal de proveedores para esta estación permanecerá bloqueado
    para nuevas entregas.
</p>

<p>Atentamente,<br>Sistema Portal de Proveedores — TotalGas</p>
