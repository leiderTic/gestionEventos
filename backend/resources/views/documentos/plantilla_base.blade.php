<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo', 'Documento Institucional - UCPP')</title>
    @php
        $logoUcpp = base64_encode(file_get_contents(public_path('image/plantilla/logo-ucpp.jpg')));
        $fondoAgua = base64_encode(file_get_contents(public_path('image/plantilla/fondo-agua.png')));
        $logoMefp = base64_encode(file_get_contents(public_path('image/plantilla/logo-mefp.png')));
        $infoOficina = base64_encode(file_get_contents(public_path('image/plantilla/info-oficina.png')));
        $fontRegular = base64_encode(file_get_contents(public_path('fonts/CenturyGothic.ttf')));
        $fontBold = base64_encode(file_get_contents(public_path('fonts/CenturyGothic-Bold.ttf')));
        $fontItalic = base64_encode(file_get_contents(public_path('fonts/CenturyGothic-Italic.ttf')));
        $fontBoldItalic = base64_encode(file_get_contents(public_path('fonts/CenturyGothic-BoldItalic.ttf')));
    @endphp
    <style>
        @font-face {
            font-family: "Century Gothic";
            src: url("data:font/truetype;base64,{{ $fontRegular }}");
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: "Century Gothic";
            src: url("data:font/truetype;base64,{{ $fontBold }}");
            font-weight: bold;
            font-style: normal;
        }
        @font-face {
            font-family: "Century Gothic";
            src: url("data:font/truetype;base64,{{ $fontItalic }}");
            font-weight: normal;
            font-style: italic;
        }
        @font-face {
            font-family: "Century Gothic";
            src: url("data:font/truetype;base64,{{ $fontBoldItalic }}");
            font-weight: bold;
            font-style: italic;
        }

        @page {
            size: letter portrait;
            margin-top: 3.76cm;
            margin-bottom: 2.6cm;
            margin-left: 2.54cm;
            margin-right: 2.54cm;
        }

        body {
            font-family: "Century Gothic", Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            line-height: 1.12;
            margin: 0;
            padding: 0;
        }

        /* MARCA DE AGUA CENTRADA */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 14.5cm;
            height: 14.5cm;
            margin-top: -7.25cm;
            margin-left: -7.25cm;
            z-index: -1000;
            text-align: center;
        }
        .watermark img {
            width: 100%;
            height: auto;
        }

        /* ENCABEZADO FIJO */
        header {
            position: fixed;
            top: -2.85cm;
            left: -0.54cm;
            height: 2.5cm;
        }
        .header-logo {
            height: 1.63cm;
            width: auto;
        }

        /* PIE DE PÁGINA FIJO */
        footer {
            position: fixed;
            bottom: -3.05cm;
            left: -1.34cm;
            right: -0.52cm;
            height: 2.0cm;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .footer-table td {
            vertical-align: bottom;
            padding: 0;
        }
        .footer-logo-mefp {
            height: 1.27cm;
            width: auto;
        }
        .footer-info-oficina {
            height: 1.25cm;
            width: auto;
            float: right;
        }

        /* CONTENIDO DINÁMICO */
        .content-body {
            width: 100%;
        }

        /* UTILITARIOS COMUNES */
        p {
            margin-top: 0;
            margin-bottom: 5px;
        }
        .text-justify { text-align: justify; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        .underline { text-decoration: underline; }
        .uppercase { text-transform: uppercase; }
    </style>
    @yield('estilos_adicionales')
</head>
<body>

    @hasSection('sin_marca_agua')
    @else
    <div class="watermark">
        <img src="data:image/png;base64,{{ $fondoAgua }}" alt="Marca de agua UCPP" />
    </div>
    @endif

    @hasSection('sin_encabezado')
    @else
    <header>
        <img src="data:image/jpeg;base64,{{ $logoUcpp }}" class="header-logo" alt="Logo UCPP" />
    </header>
    @endif

    @hasSection('sin_pie')
    @else
    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 48%; text-align: left;">
                    <img src="data:image/png;base64,{{ $logoMefp }}" class="footer-logo-mefp" alt="Logo Ministerio" />
                </td>
                <td style="width: 52%; text-align: right;">
                    <img src="data:image/png;base64,{{ $infoOficina }}" class="footer-info-oficina" alt="Oficina Central" />
                </td>
            </tr>
        </table>
    </footer>
    @endif

    <div class="content-body">
        @yield('contenido')
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("helvetica", "normal");
            $size = 8.5;
            $color = array(0.15, 0.15, 0.15);
            $pdf->page_text(485, 680, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, $size, $color);
        }
    </script>
</body>
</html>
