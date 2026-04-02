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

        .top-bar {
            height: 8px;
            background: #014C9B;
            margin-bottom: 15px;
        }

        .header table {
            width: 100%;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #014C9B;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
        }

        .meta-box {
            background: #f3f4f6;
            padding: 12px;
            border-left: 5px solid #0D9F3A;
            font-size: 11px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .summary td {
            padding: 7px;
            border: 1px solid #e5e7eb;
        }

        .label {
            background: #f9fafb;
            font-weight: bold;
            color: #014C9B;
            width: 25%;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            color: #014C9B;
            border-bottom: 2px solid #014C9B;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .section-content {
            padding: 12px;
            background: #fafafa;
            border-left: 4px solid #0D9F3A;
        }

        .signature {
            margin-top: 60px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 250px;
            margin: 0 auto 5px auto;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: center;
            color: #6b7280;
            border-top: 1px solid #ccc;
            padding-top: 10px;
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
                    <img src="{{ public_path('images/logo.png') }}" style="height:60px;">
                    <div class="title">Memoria Técnica de Tarea</div>
                    <div class="subtitle">Sistema de Mesa de Ayuda</div>
                </td>

                <td width="40%">
                    <div class="meta-box">
                        <b>Folio:</b> {{ $tarea->id_tarea }}<br>
                        <b>Fecha creación:</b> {{ \Carbon\Carbon::parse($tarea->fecha_creacion)->format('d/m/Y H:i') }}<br>
                        <b>Fecha cierre:</b> {{ now()->format('d/m/Y H:i') }}<br>
                        <b>Sucursal:</b> {{ $tarea->sucursal }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- RESUMEN -->
    <table class="summary">
        <tr>
            <td class="label">Título</td>
            <td>{{ $tarea->titulo }}</td>
            <td class="label">Prioridad</td>
            <td>
                @if($tarea->prioridad == 1) 🔴 Alta
                @elseif($tarea->prioridad == 2) 🟠 Media
                @else 🟢 Baja
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Estado</td>
            <td>Finalizado</td>
            <td class="label">Fecha límite</td>
            <td>{{ \Carbon\Carbon::parse($tarea->fecha_limite)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- PROBLEMATICA -->
    <div class="section">
        <div class="section-title">1. Problemática</div>
        <div class="section-content">
            {{ $tarea->problematica }}
        </div>
    </div>

    <!-- ACTIVIDADES -->
    <div class="section">
        <div class="section-title">2. Actividades Realizadas</div>
        <div class="section-content">
            {{ $tarea->descripcion }}
        </div>
    </div>

    <!-- MATERIALES -->
    <div class="section">
        <div class="section-title">3. Materiales Utilizados</div>
        <div class="section-content">
            {{ $tarea->materiales ?? 'No especificado' }}
        </div>
    </div>

    <!-- SOLUCION -->
    <div class="section">
        <div class="section-title">4. Solución Aplicada</div>
        <div class="section-content">
            {{ $solucion }}
        </div>
    </div>

    <!-- FIRMA -->
    <div class="signature">
        <div class="signature-line"></div>
        Técnico responsable<br>
        Área de Soporte TI
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Documento generado automáticamente por el sistema de tareas.<br>
        Válido sin firma autógrafa.
    </div>

</div>
</body>
</html>