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

        .container {
            padding: 30px;
        }

        /* BARRA SUPERIOR CORPORATIVA */
        .top-bar {
            height: 8px;
            background: #014C9B;
            margin-bottom: 15px;
        }

        /* HEADER */
        .header {
            margin-bottom: 25px;
        }

        .header table {
            width: 100%;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #014C9B;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        .meta-box {
            background: #f3f4f6;
            padding: 12px;
            border-left: 5px solid #0D9F3A;
            font-size: 11px;
        }

        /* TABLA RESUMEN */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
            font-size: 11px;
        }

        .summary-table td {
            padding: 7px;
            border: 1px solid #e5e7eb;
        }

        .summary-label {
            background: #f9fafb;
            font-weight: bold;
            width: 25%;
            color: #014C9B;
        }

        /* SECCIONES */
        .section {
            margin-bottom: 22px;
        }

        .section-title {
            font-weight: bold;
            font-size: 13px;
            color: #014C9B;
            padding-bottom: 5px;
            border-bottom: 2px solid #014C9B;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .section-content {
            padding: 12px;
            background: #fafafa;
            border-left: 4px solid #0D9F3A;
            line-height: 1.6;
            text-align: justify;
        }

        /* FIRMA */
        .signature {
            margin-top: 60px;
            text-align: center;
            font-size: 11px;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 260px;
            margin: 0 auto 6px auto;
        }

        /* FOOTER */
        .footer {
            margin-top: 40px;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="top-bar"></div>

        <!-- HEADER -->
        <div class="header">
            <table>
                <tr>
                    <td width="60%">
                        <!-- Si usas DomPDF en Laravel -->
                        <img src="{{ public_path('images/logo.png') }}" style="height:60px; margin-bottom:10px;">

                        <div class="report-title">
                            MEMORIA TÉCNICA DE ATENCIÓN
                        </div>
                        <div class="report-subtitle">
                            Sistema de Mesa de Ayuda – Soporte TI
                        </div>
                    </td>

                    <td width="40%">
                        <div class="meta-box">
                            <b>Folio:</b> {{ $ticket->id_ticket }}<br>
                            <b>Fecha creación:</b> {{ \Carbon\Carbon::parse($ticket->fecha_creacion)->format('d/m/Y H:i') }}<br>
                            <b>Fecha cierre:</b> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}<br>
                            <b>Sucursal:</b> {{ $ticket->sucursal }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- TABLA RESUMEN TÉCNICO -->
        <table class="summary-table">
            <tr>
                <td class="summary-label">Título</td>
                <td>{{ $ticket->titulo }}</td>
                <td class="summary-label">Categoría</td>
                <td>{{ $ticket->categoria ?? 'No especificada' }}</td>
            </tr>
            <tr>
                <td class="summary-label">Subtipo</td>
                <td>{{ $ticket->tipo_problema ?? 'No especificado' }}</td>
                <td class="summary-label">Prioridad</td>
                <td>{{ $ticket->prioridad }}</td>
            </tr>
            <tr>
                <td class="summary-label">Estado Final</td>
                <td>Cerrado</td>
                <td class="summary-label">Tiempo resolución</td>
                <td>{{ $ticket->tiempo_resolucion_minutos ?? 0 }} minutos</td>
            </tr>
            <tr>
                <td class="summary-label">Técnico responsable</td>
                <td colspan="3">
                    {{ $ticket->tecnico_nombre ?? auth()->user()->nombre }}
                </td>
            </tr>
        </table>

        <!-- 1. PROBLEMA -->
        <div class="section">
            <div class="section-title">1. Problema Reportado</div>
            <div class="section-content">
                {{ $ticket->descripcion }}
            </div>
        </div>

        <!-- 2. DIAGNÓSTICO -->
        <div class="section">
            <div class="section-title">2. Diagnóstico Técnico</div>
            <div class="section-content">
                {{ $ticket->diagnostico ?? 'No se registró diagnóstico técnico.' }}
            </div>
        </div>

        <!-- 3. SOLUCIÓN -->
        <div class="section">
            <div class="section-title">3. Solución Aplicada</div>
            <div class="section-content">
                {{ $ticket->solucion ?? 'No se registró solución detallada.' }}
            </div>
        </div>

        <!-- 4. OBSERVACIONES -->
        @if(!empty($ticket->observaciones))
        <div class="section">
            <div class="section-title">4. Observaciones</div>
            <div class="section-content">
                {{ $ticket->observaciones }}
            </div>
        </div>
        @endif

        <!-- FIRMA -->
        <div class="signature">
            <div class="signature-line"></div>
            {{ $ticket->tecnico_nombre ?? auth()->user()->nombre }}<br>
            Área de Soporte TI
        </div>

        <!-- FOOTER -->
        <div class="footer">
            Documento técnico generado automáticamente por el Sistema Interno de Mesa de Ayuda.<br>
            Documento digital válido sin firma autógrafa.
        </div>

    </div>
</body>

</html>