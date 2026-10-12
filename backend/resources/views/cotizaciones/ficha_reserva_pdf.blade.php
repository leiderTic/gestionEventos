<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Reserva - {{ $datos['codigo_reserva'] }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.3;
            background-color: #ffffff;
            position: relative;
        }

        /* Watermark institucional idéntico a cotizaciones */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.04;
            width: 500px;
            z-index: -1000;
        }

        /* Franja tricolor institucional (Top y Footer) */
        .brand-stripes {
            height: 12px;
            width: 100%;
            display: table;
            table-layout: fixed;
        }
        .stripe {
            display: table-cell;
            height: 12px;
        }
        .stripe-red { background-color: #ef4444; }
        .stripe-yellow { background-color: #fbbf24; }
        .stripe-green { background-color: #15803d; }

        /* Barra Principal Verde Oscuro */
        .main-header-bar {
            background-color: #06352c;
            color: #ffffff;
            padding: 8px 30px;
            display: table;
            width: 100%;
            box-sizing: border-box;
        }
        .header-col-left {
            display: table-cell;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            vertical-align: middle;
            color: #facc15;
            width: 38%;
        }
        .header-col-center {
            display: table-cell;
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            letter-spacing: 1px;
            vertical-align: middle;
            color: #ffffff;
            width: 24%;
        }
        .header-col-right {
            display: table-cell;
            text-align: right;
            font-size: 10px;
            font-weight: 600;
            vertical-align: middle;
            color: #e2e8f0;
            width: 38%;
        }

        /* Contenido del documento */
        .page-body {
            padding: 16px 35px 50px 35px;
        }

        /* Logo y Datos Institucionales Superiores */
        .top-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .top-info-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .logo-img {
            height: 60px;
        }
        .company-header-info {
            text-align: right;
            color: #475569;
            font-size: 8.5px;
        }
        .company-header-info h2 {
            margin: 0 0 2px 0;
            color: #06352c;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
        }
        .ejecutivo-tag {
            margin-top: 3px;
            font-size: 9.5px;
            color: #0f172a;
        }

        /* Barras de Sección Verde Institucional */
        .section-bar {
            background-color: #06352c;
            color: #ffffff;
            padding: 4px 10px;
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        /* Tablas de Información (2 columnas) */
        .info-grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .info-grid-table td {
            padding: 3px 0;
            vertical-align: top;
            width: 50%;
            border: none;
        }
        .field-label {
            font-size: 7.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1px;
        }
        .field-value {
            font-size: 9.5px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Tabla de Estado de Pagos */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            margin-bottom: 0;
        }
        .payments-table th {
            text-align: left;
            padding: 5px 8px;
            font-size: 8px;
            font-weight: 700;
            color: #475569;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            text-transform: capitalize;
        }
        .payments-table td {
            padding: 5px 8px;
            font-size: 9px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .payments-table tr:last-child td {
            border-bottom: none;
        }
        .monto-col {
            font-weight: 800;
            color: #065f46;
        }
        .status-badge-pagado {
            color: #15803d;
            font-weight: 700;
        }
        .status-badge-pendiente {
            color: #b45309;
            font-weight: 700;
        }

        /* Barra de Total Cotización */
        .total-bar {
            background-color: #06352c;
            color: #ffffff;
            padding: 6px 10px;
            display: table;
            width: 100%;
            box-sizing: border-box;
            margin-top: 2px;
            margin-bottom: 10px;
        }
        .total-bar-label {
            display: table-cell;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .total-bar-amount {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            font-weight: 900;
            vertical-align: middle;
            color: #ffffff;
        }

        /* Caja de Advertencia Reglamentaria */
        .warning-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 16px;
            color: #92400e;
            font-size: 8px;
            line-height: 1.35;
        }
        .warning-icon {
            font-weight: 900;
            color: #d97706;
            margin-right: 4px;
        }

        /* Firmas Institucionales */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 22px;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        .signatures-table td {
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
            border: none;
            width: 33.33%;
        }
        .signature-line {
            border-top: 1px solid #1e293b;
            margin-bottom: 4px;
            width: 85%;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-name {
            font-size: 9px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1px;
        }
        .signature-title {
            font-size: 7.5px;
            color: #64748b;
        }

        /* Footer Fijo idéntico a cotizaciones */
        .footer-fixed {
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #cbd5e1;
            background-color: #ffffff;
        }
        .footer-table {
            width: 100%;
            padding: 6px 35px;
        }
        .footer-table td {
            border: none;
            font-size: 7.5px;
            color: #64748b;
        }
        .footer-center {
            text-align: center;
            color: #15803d;
            font-weight: bold;
        }
        .footer-right {
            text-align: right;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <!-- Marca de agua (Watermark) con máxima compatibilidad DomPDF -->
    @if(!empty($logoBase64))
        <img src="{{ $logoBase64 }}" class="watermark" alt="Watermark">
    @endif

    <!-- Franja Tricolor Superior -->
    <div class="brand-stripes">
        <div class="stripe stripe-red"></div>
        <div class="stripe stripe-yellow"></div>
        <div class="stripe stripe-green"></div>
    </div>

    <!-- Barra Principal Verde Oscuro -->
    <div class="main-header-bar">
        <div class="header-col-left">CONFIRMACIÓN DE RESERVA</div>
        <div class="header-col-center">{{ $datos['codigo_reserva'] }}</div>
        <div class="header-col-right">{{ $datos['fecha_emision'] }}</div>
    </div>

    <div class="page-body">

        <!-- Logo y Datos Institucionales -->
        <table class="top-info-table">
            <tr>
                <td style="width: 50%;">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="logo-img" alt="Campo Ferial Chuquiago Marka">
                    @else
                        <div style="font-size: 16px; font-weight: 900; color: #06352c; letter-spacing: 1px;">
                            CAMPO FERIAL<br><span style="color: #d97706;">Chuquiago Marka</span>
                        </div>
                    @endif
                </td>
                <td style="width: 50%;" class="company-header-info">
                    <h2>Unidad de Coordinación de Programas y Proyectos</h2>
                    <span>www.chuquiagomarka.com.bo &bull; www.ucpp.gob.bo</span><br>
                    <div class="ejecutivo-tag">
                        Ejecutivo Comercial: <strong>{{ $datos['ejecutivo_comercial'] }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        <!-- 1. DATOS DE LA RESERVA -->
        <div class="section-bar">DATOS DE LA RESERVA</div>
        <table class="info-grid-table">
            <tr>
                <td>
                    <div class="field-label">N° RESERVA</div>
                    <div class="field-value">{{ $datos['codigo_reserva'] }}</div>
                </td>
                <td>
                    <div class="field-label">COTIZACIÓN</div>
                    <div class="field-value">{{ $datos['codigo_cotizacion'] }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-label">CLIENTE</div>
                    <div class="field-value">{{ $datos['cliente_nombre'] }}</div>
                </td>
                <td>
                    <div class="field-label">NIT</div>
                    <div class="field-value">{{ $datos['nit'] }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-label">CONTACTO</div>
                    <div class="field-value">{{ $datos['contacto_nombre'] }}</div>
                </td>
                <td>
                    <div class="field-label">TELÉFONO</div>
                    <div class="field-value">{{ $datos['telefono'] }}</div>
                </td>
            </tr>
        </table>

        <!-- 2. DATOS DEL EVENTO -->
        <div class="section-bar">DATOS DEL EVENTO</div>
        <table class="info-grid-table">
            <tr>
                <td>
                    <div class="field-label">TIPO</div>
                    <div class="field-value">{{ $datos['tipo_evento'] }}</div>
                </td>
                <td>
                    <div class="field-label">FECHAS</div>
                    <div class="field-value">{{ $datos['fechas_evento'] }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="field-label">ESPACIOS</div>
                    <div class="field-value">{{ $datos['espacios'] }}</div>
                </td>
            </tr>
        </table>

        <!-- 3. ESTADO DE PAGOS -->
        <div class="section-bar">ESTADO DE PAGOS</div>
        <table class="payments-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Concepto</th>
                    <th style="width: 22%;">Monto</th>
                    <th style="width: 20%;">Estado</th>
                    <th style="width: 33%;">Comprobante</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight: 700;">Anticipo 20%</td>
                    <td class="monto-col">Bs {{ number_format($datos['anticipo_monto'], 2, ',', '.') }}</td>
                    <td>
                        <span class="status-badge-pagado">&#x2714; {{ $datos['anticipo_estado'] }}</span>
                    </td>
                    <td>{{ $datos['anticipo_comprobante'] }}</td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Saldo 80%</td>
                    <td class="monto-col">Bs {{ number_format($datos['saldo_monto'], 2, ',', '.') }}</td>
                    <td>
                        <span class="status-badge-pendiente">&#x23F3; Pendiente</span>
                    </td>
                    <td>—</td>
                </tr>
                <tr>
                    <td style="font-weight: 700;">Garantía 20%</td>
                    <td class="monto-col">Bs {{ number_format($datos['garantia_monto'], 2, ',', '.') }}</td>
                    <td>
                        <span class="status-badge-pendiente">&#x23F3; Pendiente</span>
                    </td>
                    <td style="font-size: 8px;">Fdo.Rotativo N°10000041433686</td>
                </tr>
            </tbody>
        </table>

        <!-- Barra de Total Cotización -->
        <div class="total-bar">
            <div class="total-bar-label">TOTAL COTIZACIÓN</div>
            <div class="total-bar-amount">Bs {{ number_format($datos['total_cotizacion'], 2, '.', '') }}</div>
        </div>

        <!-- Cláusula Reglamentaria Art. 42 -->
        <div class="warning-box">
            <span class="warning-icon">&#x26A0;</span>
            <strong>El incumplimiento en los plazos establecidos dejará sin efecto la presente reserva, sin derecho a devolución del anticipo (Art.42 Reglamento Operativo UCFCHM).</strong>
        </div>

        <!-- Firmas Institucionales -->
        <table class="signatures-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $datos['ejecutivo_comercial'] }}</div>
                    <div class="signature-title">Ejecutivo Comercial</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $datos['jefe_ucfchm'] }}</div>
                    <div class="signature-title">Jefe UCFCHM</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $datos['contacto_nombre'] }}</div>
                    <div class="signature-title">Representante del Cliente</div>
                </td>
            </tr>
        </table>

    </div>

    <!-- Footer Fijo idéntico al PDF de cotizaciones -->
    <div class="footer-fixed">
        <table class="footer-table">
            <tr>
                <td>Campo Ferial Chuquiago Marka - Gestión {{ date('Y') }} &middot; Art. 40 Reglamento</td>
                <td class="footer-center">www.chuquiagomarka.com.bo - www.ucpp.gob.bo</td>
                <td class="footer-right">{{ $datos['codigo_reserva'] }}</td>
            </tr>
        </table>
        <div class="brand-stripes">
            <div class="stripe stripe-red"></div>
            <div class="stripe stripe-yellow"></div>
            <div class="stripe stripe-green"></div>
        </div>
    </div>

</body>
</html>
