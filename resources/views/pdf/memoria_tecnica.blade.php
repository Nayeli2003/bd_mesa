<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
    }

    .header {
        width: 100%;
        margin-bottom: 20px;
    }

    .logo {
        float: left;
        width: 30%;
    }

    .title {
        float: left;
        width: 40%;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
    }

    .info {
        float: right;
        width: 30%;
        font-size: 12px;
    }

    .clear {
        clear: both;
    }

    .section {
        margin-bottom: 15px;
    }

    .vertical-label {
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        background: #333;
        color: white;
        padding: 5px;
        font-weight: bold;
        text-align: center;
    }

    .box {
        border: 1px solid #000;
        padding: 6px;
        min-height: 60px;
    }

    .line-box {
        border-bottom: 1px solid #000;
        min-height: 20px;
        margin-bottom: 5px;
    }

    .stars {
        font-size: 14px;
    }

    .signature {
        margin-top: 40px;
        text-align: center;
    }

    .signature div {
        display: inline-block;
        width: 30%;
        text-align: center;
    }

</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="logo">
        <img src="{{ public_path('logo.png') }}" height="60">
    </div>

    <div class="title">
        MEMORIA<br>TÉCNICA
    </div>

    <div class="info">
        <b>Folio:</b> {{ $ticket->id_ticket }} <br>
        <b>Fecha:</b> {{ \Carbon\Carbon::now()->format('d/m/Y') }} <br>
        <b>Sucursal:</b> {{ $ticket->sucursal }}
    </div>
    <div class="clear"></div>
</div>

<!-- PROBLEMA -->
<div class="section">
    <table width="100%">
        <tr>
            <td width="5%" class="vertical-label">Problema</td>
            <td width="95%">
                <div class="box">
                    <b>Reporte:</b><br>
                    {{ $ticket->titulo }}
                    <br><br>

                    <b>Datos del equipo:</b><br>
                    ________________________________
                    <br><br>

                    <b>Diagnóstico y causas:</b><br>
                    {{ $ticket->descripcion }}
                    <br><br>

                    <b>Antecedentes:</b><br>
                    ________________________________
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- SOLUCIÓN -->
<div class="section">
    <table width="100%">
        <tr>
            <td width="5%" class="vertical-label">Solución</td>
            <td width="95%">
                <div class="box">
                    <b>Solución aplicada:</b><br>
                    {{ $ticket->solucion ?? 'Sin solución registrada' }}
                    <br><br>

                    <b>Tiempo de resolución:</b><br>
                    {{ $ticket->tiempo_resolucion_minutos ?? 0 }} minutos
                    <br><br>

                    <b>Material utilizado:</b><br>
                    ________________________________
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- EVALUACIÓN -->
<div class="section">
    <b>Evaluación:</b><br><br>

    Conocimiento:
    <span class="stars">★★★★★</span><br><br>

    Eficiencia:
    <span class="stars">★★★★★</span><br><br>

    Limpieza:
    <span class="stars">★★★★★</span><br><br>

    Presentación:
    <span class="stars">★★★★★</span><br><br>

    Velocidad:
    <span class="stars">★★★★★</span><br><br>

    Pruebas finales:
    <span class="stars">★★★★★</span>
</div>

<!-- FIRMAS -->
<div class="signature">
    <div>
        ___________________________<br>
        Encargado de sucursal
    </div>

    <div>
        ___________________________<br>
        Ingeniero
    </div>

    <div>
        ___________________________<br>
        Verificación
    </div>
</div>

</body>
</html>
