<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <!-- App favicon -->
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">

    <title>{{ $announcement->title }}</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            @top-right {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }

        .document-header {
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
            margin-bottom: 25px;
            position: relative;
        }

        .company-logo {
            float: right;
            max-height: 60px;
            max-width: 150px;
        }

        .document-type {
            color: #3498db;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0 15px 0;
            line-height: 1.2;
        }

        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .priority-1 { background-color: #d5f4e6; color: #27ae60; }
        .priority-2 { background-color: #e8f4f8; color: #3498db; }
        .priority-3 { background-color: #fdf2e9; color: #e67e22; }
        .priority-4 { background-color: #fadbd8; color: #e74c3c; }

        .metadata-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #3498db;
            margin-bottom: 25px;
            border-radius: 0 4px 4px 0;
        }

        .metadata-row {
            display: flex;
            margin-bottom: 8px;
        }

        .metadata-row:last-child {
            margin-bottom: 0;
        }

        .metadata-label {
            font-weight: bold;
            color: #34495e;
            min-width: 120px;
            display: inline-block;
        }

        .metadata-value {
            color: #2c3e50;
            flex: 1;
        }

        .cover-section {
            text-align: center;
            margin: 25px 0;
        }

        .cover-image {
            max-width: 100%;
            max-height: 300px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .content-section {
            margin-top: 25px;
        }

        .content-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
        }

        .content-text {
            text-align: justify;
            line-height: 1.6;
            font-size: 12px;
            white-space: pre-wrap;
            margin-bottom: 20px;
        }

        .footer-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #bdc3c7;
            font-size: 10px;
            color: #7f8c8d;
        }

        .validity-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 12px;
            margin: 20px 0;
            font-size: 11px;
        }

        .validity-notice strong {
            color: #856404;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Status indicator */
        .status-indicator {
            position: absolute;
            top: 0;
            right: 0;
            padding: 4px 8px;
            border-radius: 0 0 0 8px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-active {
            background-color: #d5f4e6;
            color: #27ae60;
        }

        .status-inactive {
            background-color: #fadbd8;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <!-- Header del documento -->
    <div class="document-header clearfix">

        <img src="{{ $logoPath }}" alt="TotalGas" width="190" style="min-height:55px!important;">
        <br>

        <div class="document-type" style="padding-top: 15px">Comunicado Oficial</div>
        <h1 class="document-title">{{ $announcement->title }}</h1>

        <!-- Indicador de prioridad -->
        <div class="priority-badge priority-2">
            Publicado el {{ optional($announcement->published_at)->format('d/m/Y H:i') ?? 'No definida' }}
        </div>

        <!-- Indicador de prioridad -->
        <div class="priority-badge priority-{{ $announcement->priority }}">
            @switch($announcement->priority)
                @case(1) Prioridad Baja @break
                @case(2) Prioridad Normal @break
                @case(3) Prioridad Alta @break
                @case(4) Prioridad Urgente @break
                @default Prioridad Normal
            @endswitch
        </div>

        <!-- Indicador de estado -->
        <div class="status-indicator {{ $announcement->is_active ? 'status-active' : 'status-inactive' }}">
            {{ $announcement->is_active ? 'Activo' : 'Inactivo' }}
        </div>
    </div>

    {{-- Imagen de portada --}}
    @if(!empty($coverBase64))
        <div class="cover-section">
            <img src="{{ $coverBase64 }}" alt="Imagen del comunicado" class="cover-image">
        </div>
    @endif

    <!-- Contenido principal -->
    <div class="content-section">
        <div class="content-title">Estimado Proveedor:</div>
        <div class="content-text">{{ $announcement->description }}</div>
    </div>

    <!-- Aviso de validez -->
    @if($announcement->visible_until && $announcement->visible_until->isFuture())
    <div class="validity-notice">
        <strong>⚠️ Aviso de Validez:</strong>
        Este comunicado es válido hasta el {{ $announcement->visible_until->format('d/m/Y') }} a las {{ $announcement->visible_until->format('H:i') }}.
        @if($announcement->visible_until->diffInDays(now()) <= 7)
            <strong>Próximo a vencer.</strong>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer-section">
        <div style="text-align: center;">
            <strong>TotalGas Gasolineras</strong><br>
            <p>Av. Rafael Perez Serna 755, Col. Partido romero | 32330 Ciudad Juárez, Chihuahua | Tel: +52 (656) 289 6600 | www.totalgas.com</p>
            <p></p>
            <br>
            <em>Documento generado automáticamente el {{ now()->format('d/m/Y H:i') }}</em>
        </div>
    </div>
</body>
</html>
