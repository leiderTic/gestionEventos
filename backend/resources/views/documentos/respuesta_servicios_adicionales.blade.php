@extends('documentos.plantilla_base')

@section('titulo', 'Respuesta a Solicitud de Cotización Servicios Adicionales')

@section('estilos_adicionales')
<style>
    body {
        line-height: 1.15;
    }
    p {
        margin-top: 0;
        margin-bottom: 6px;
    }
    .lista-vineta {
        margin-left: 22px;
        margin-bottom: 8px;
        text-align: justify;
        font-size: 10pt;
        position: relative;
    }
    .lista-vineta::before {
        content: "•";
        position: absolute;
        left: -14px;
        font-weight: bold;
    }
    .seccion-inciso {
        margin-left: 16px;
        margin-bottom: 4px;
        text-align: justify;
        font-size: 10pt;
    }
    .sub-inciso {
        margin-left: 22px;
        margin-top: 2px;
    }
</style>
@endsection

@section('contenido')
{{-- PÁGINA 1 --}}
<div style="text-align: left; margin-bottom: 10px;">
    <span>La Paz, {{ $fecha_formateada }}</span><br>
    <span class="bold">{{ $prefijo_cite }} {{ $cite }}/{{ $anio }}</span>
</div>

<div style="margin-bottom: 10px; line-height: 1.15;">
    Señor:<br>
    <span style="color: #000;">{{ $destinatario_nombre }}</span><br>
    <span class="bold uppercase" style="color: #000;">{{ $destinatario_cargo_institucion }}</span><br>
    <span style="text-decoration: underline;">Presente</span>. -
</div>

<div style="text-align: right; margin: 8px 0 12px 0; font-weight: bold; line-height: 1.15;">
    <span style="text-decoration: underline;">REF.: RESPUESTA A SOLICITUD DE COTIZACIÓN SERVICIOS ADICIONALES</span>
</div>

<p class="text-justify" style="margin-bottom: 8px;">De mi consideración,</p>

<p class="text-justify" style="margin-bottom: 10px;">
    A tiempo de saludarlo con la mayor atención, habiéndose recibido su nota de solicitud de cotización de servicios adicionales para el alquiler del <b>{{ $espacios_texto }} {{ $fechas_evento_texto }}</b>, adjunto a la presente se remite la cotización correspondiente.
</p>

<p class="text-justify" style="margin-bottom: 8px;">
    Para efectos de formalizar el arrendamiento de los servicios adicionales requeridos, deberá efectuar dentro del plazo de <b>{{ $plazo }}</b> computables a partir de la recepción de la presente:
</p>

<div class="lista-vineta">
    Depósito bancario original o comprobante de transferencia electrónica por el total del arrendamiento de los servicios adicionales a la Cuenta corriente fiscal del <b>{{ $cuenta_anticipo_banco }} N° {{ $cuenta_anticipo_numero }} “{{ $cuenta_anticipo_titular }}”</b>, consignando en la glosa “SERVICIOS ADICIONALES – <span class="uppercase">{{ $evento_nombre }}</span>”.
</div>

<div class="lista-vineta" style="margin-bottom: 12px;">
    Depósito bancario original o comprobante de transferencia electrónica correspondiente al {{ $porcentaje_garantia }}% del monto total cotizado, por concepto de <b>GARANTÍA</b>, a la Cuenta Corriente Fiscal del <b>{{ $cuenta_garantia_banco }} N° {{ $cuenta_garantia_numero }} “{{ $cuenta_garantia_titular }}”</b>, consignando en la glosa “SERVICIOS ADICIONALES - <span class="uppercase">{{ $evento_nombre }}</span>”. Este pago no forma parte del anticipo señalado precedentemente.
</div>

<p class="text-justify" style="margin-bottom: 4px;">
    Para efectos de la elaboración y suscripción de la Orden de Servicios Adicionales, deberá remitirse la documentación que corresponda de acuerdo con la naturaleza jurídica del solicitante, conforme al siguiente detalle:
</p>

<div class="seccion-inciso" style="margin-bottom: 0;">
    <div style="margin-left: 4px;">
        <b>a) Para personas naturales:</b>
        <div class="sub-inciso">
            i. &nbsp;&nbsp;Cédula de identidad vigente en formato digital.
        </div>

        <b style="display: block; margin-top: 4px;">b) Para personas naturales o jurídicas dedicadas al comercio:</b>
    </div>
</div>

{{-- PÁGINA 2 --}}
<div style="page-break-before: always;"></div>

<div class="seccion-inciso" style="margin-top: 0;">
    <div style="margin-left: 4px;">
        <div class="sub-inciso" style="margin-top: 0;">
            i. &nbsp;&nbsp;&nbsp;Copia del Registro de Comercio emitido por el Servicio Plurinacional de Registro de Comercio (SEPREC) vigente.<br>
            ii. &nbsp;&nbsp;Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. &nbsp;Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. &nbsp;Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>

        <b style="display: block; margin-top: 6px;">c) Para representaciones diplomáticas:</b>
        <div class="sub-inciso">
            i. &nbsp;&nbsp;&nbsp;Copia Credenciales emitidas por el Ministerio de Relaciones Exteriores del Estado Plurinacional de Bolivia.<br>
            ii. &nbsp;&nbsp;Certificación Electrónica del Número de Identificación Tributaria (NIT), (cuando corresponda).<br>
            iii. &nbsp;Copia de documento de identidad del representante diplomático.
        </div>

        <b style="display: block; margin-top: 6px;">d) Para fundaciones y/o asociaciones:</b>
        <div class="sub-inciso">
            i. &nbsp;&nbsp;&nbsp;Copia de Resolución de otorgación de personería jurídica.<br>
            ii. &nbsp;&nbsp;Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. &nbsp;Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. &nbsp;Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>

        <b style="display: block; margin-top: 6px;">e) Para sociedades civiles:</b>
        <div class="sub-inciso">
            i. &nbsp;&nbsp;&nbsp;Testimonio de constitución.<br>
            ii. &nbsp;&nbsp;Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. &nbsp;Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. &nbsp;Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>

        <b style="display: block; margin-top: 6px;">f) Para otros solicitantes:</b>
        <div class="sub-inciso">
            i. &nbsp;&nbsp;&nbsp;Documento que acredite capacidad jurídica para contraer obligaciones de acuerdo a su naturaleza jurídica.<br>
            ii. &nbsp;&nbsp;Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. &nbsp;Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. &nbsp;Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>
    </div>
</div>

{{-- PÁGINA 3 --}}
<div style="page-break-before: always;"></div>

<p class="text-justify" style="margin-top: 0; margin-bottom: 10px;">
    Para la coordinación de los aspectos operativos y administrativos relacionados con el desarrollo del evento, se encuentran habilitados los siguientes canales oficiales:
</p>

<div style="margin-bottom: 14px;">
    <b>Teléfono:</b> {{ $telefono }}.<br>
    <b>Correo electrónico:</b> <span style="text-decoration: underline; color: #0044cc;">{{ $correo_electronico }}</span>
</div>

<p class="text-justify" style="margin-bottom: 12px;">
    Sin otro particular, reitero a usted las seguridades de mi consideración y estima.
</p>

<p style="margin-bottom: 35px;">Atentamente,</p>

<div style="margin-top: 20px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: bottom; font-size: 8pt; line-height: 1.15; color: #222;">
                @if(!empty($hoja_ruta))
                <span>H.R. {{ $hoja_ruta }}</span><br>
                @endif
                @if(!empty($moscas))
                <span>{{ $moscas }}</span><br>
                @endif
                <span>Adj: Cotización</span><br>
                <span>cc.: Arch</span>
            </td>
            <td style="width: 50%; vertical-align: bottom; text-align: right;">
                @if(!empty($qr_base64))
                <img src="data:image/svg+xml;base64,{{ $qr_base64 }}" style="width: 2.2cm; height: 2.2cm;" alt="Código QR" />
                @endif
            </td>
        </tr>
    </table>
</div>
@endsection
