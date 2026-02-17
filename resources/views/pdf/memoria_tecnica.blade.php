<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #6b7280;
        }

        .info {
            text-align: right;
            font-size: 11px;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            background: #111827;
            color: white;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 12px;
        }

        .section-content {
            border: 1px solid #d1d5db;
            padding: 10px;
            min-height: 60px;
        }

        .label {
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td width="60%">
                    <div class="title">MEMORIA TÉCNICA DIGITAL</div>
                    <div class="subtitle">Mesa de Ayuda – Soporte TI</div>
                </td>
                <td width="40%" class="info">
                    <b>Folio:</b> {{ $ticket->id_ticket }}<br>
                    <b>Fecha creación:</b> {{ \Carbon\Carbon::parse($ticket->fecha_creacion)->format('d/m/Y H:i') }}<br>
                    <b>Fecha cierre:</b> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                    <b>Sucursal:</b> {{ $ticket->sucursal }}
                </td>
            </tr>
        </table>
    </div>

    <!-- INFORMACIÓN GENERAL -->
    <div class="section">
        <div class="section-title">INFORMACIÓN GENERAL</div>
        <div class="section-content">
            <span class="label">Título:</span> {{ $ticket->titulo }}<br>
            <span class="label">Prioridad:</span> {{ $ticket->prioridad }}<br>
            <span class="label">Estado Final:</span> Cerrado<br>
            <span class="label">Tiempo de resolución:</span> {{ $ticket->tiempo_resolucion_minutos ?? 0 }} minutos
        </div>
    </div>

    <!-- PROBLEMA REPORTADO -->
    <div class="section">
        <div class="section-title">PROBLEMA REPORTADO</div>
        <div class="section-content">
            {{ $ticket->descripcion }}
        </div>
    </div>

    <!-- SOLUCIÓN APLICADA -->
    <div class="section">
        <div class="section-title">SOLUCIÓN APLICADA</div>
        <div class="section-content">
            {{ $ticket->solucion ?? 'No se registró solución detallada.' }}
        </div>
    </div>

    <!-- OBSERVACIONES -->
    @if(!empty($ticket->observaciones))
    <div class="section">
        <div class="section-title">OBSERVACIONES</div>
        <div class="section-content">
            {{ $ticket->observaciones }}
        </div>
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        Documento generado automáticamente por el Sistema de Mesa de Ayuda.<br>
        Técnico responsable: {{ auth()->user()->nombre ?? 'Sistema' }}
    </div>

</body>

</html>