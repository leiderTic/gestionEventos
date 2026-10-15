@extends('documentos.plantilla_base')

@section('titulo', 'Respuesta a Solicitud de Cotización por Arrendamiento de Espacios')

@section('estilos_adicionales')
<style>
    body {
        line-height: 1.08;
    }
    p {
        margin-top: 0;
        margin-bottom: 3px;
    }
    .seccion-inciso {
        margin-left: 16px;
        margin-bottom: 2px;
        text-align: justify;
        font-size: 9.8pt;
    }
    .sub-inciso {
        margin-left: 18px;
        margin-top: 0;
    }
</style>
@endsection

@section('contenido')
{{-- PÁGINA 1 --}}
<div style="text-align: right; margin-bottom: 5px;">
    <span>La Paz, {{ $fecha_formateada }}</span><br>
    <span class="bold">{{ $prefijo_cite }} {{ $cite }}/{{ $anio }}</span>
</div>

<div style="margin-bottom: 5px; line-height: 1.10;">
    Señor:<br>
    <span style="color: #000;">{{ $destinatario_nombre }}</span><br>
    <span class="bold uppercase" style="color: #000;">{{ $destinatario_cargo_institucion }}</span><br>
    <span style="text-decoration: underline;">Presente</span>. -
</div>

<div style="text-align: right; margin: 4px 0 5px 0; font-weight: bold; line-height: 1.10;">
    REF.: RESPUESTA A SOLICITUD DE COTIZACIÓN POR ARRENDAMIENTO DE ESPACIOS - <span class="uppercase">{{ $evento_nombre }}</span>
</div>

<p class="text-justify" style="margin-bottom: 3px;">De mi consideración:</p>

<p class="text-justify" style="margin-bottom: 3px;">
    Agradeciendo la consideración del Campo Ferial Chuquiago Marka para la realización de su evento, expresamos nuestra disposición para atender sus consultas y requerimientos, brindando la orientación y coordinación necesarias para la adecuada organización y desarrollo de la actividad.
</p>

<p class="text-justify" style="margin-bottom: 3px;">
    Al respecto, de acuerdo a su solicitud, le informamos que la(s) fecha(s) solicitada(s) del <b>{{ $fechas_evento_texto }}</b> (montaje el <b>{{ $fecha_montaje_texto }}</b> y desmontaje el <b>{{ $fecha_desmontaje_texto }}</b>) se encuentra(n) disponible(s) para el desarrollo de su evento <b>{{ $evento_nombre }}</b>, a realizarse en el <b>{{ $espacios_texto }}</b> para lo cual se adjunta la cotización respectiva.
</p>

<p class="text-justify" style="margin-bottom: 2px;">
    Para efectos de formalizar el arrendamiento y proceder con la reserva de la(s) fecha(s) solicitada(s), deberá efectuar, dentro del plazo de <b>{{ $plazo }}</b> computables a partir de la recepción de la presente:
</p>

<div class="seccion-inciso" style="margin-bottom: 2px;">
    <b>A. Servicio de arrendamiento:</b> Depósito bancario original o comprobante de transferencia electrónica por el <b>{{ $porcentaje_anticipo }}% del monto total cotizado</b> de arrendamiento por concepto de <b>ANTICIPO</b> a la Cuenta corriente fiscal del Banco Unión S.A. N° {{ $cuenta_anticipo_numero }} “{{ $cuenta_anticipo_titular }}”, consignando en la glosa el nombre de su evento.
</div>

<div class="seccion-inciso" style="margin-bottom: 0;">
    <b>B. Documentación legal:</b> Se deberá remitir la siguiente documentación para la elaboración y suscripción del documento contractual, de acuerdo con la naturaleza jurídica del solicitante, conforme al siguiente detalle:
    
    <div style="margin-left: 10px; margin-top: 1px;">
        <b>a) Para personas naturales:</b>
        <div class="sub-inciso">
            i. Cédula de identidad vigente en formato digital.
        </div>

        <b style="display: block; margin-top: 1px;">b) Para personas naturales o jurídicas dedicadas al comercio:</b>
        <div class="sub-inciso">
            i. Copia del Registro de Comercio emitido por el Servicio Plurinacional de Registro de Comercio (SEPREC) vigente.
        </div>
    </div>
</div>

{{-- PÁGINA 2 --}}
<div style="page-break-before: always;"></div>

<div class="seccion-inciso" style="margin-top: 0;">
    <div style="margin-left: 10px;">
        <div class="sub-inciso" style="margin-top: 0;">
            ii. Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>

        <b style="display: block; margin-top: 2px;">c) Para representaciones diplomáticas:</b>
        <div class="sub-inciso">
            i. Copia Credenciales emitidas por el Ministerio de Relaciones Exteriores del Estado Plurinacional de Bolivia.<br>
            ii. Certificación Electrónica del Número de Identificación Tributaria (NIT), (cuando corresponda).<br>
            iii. Copia de documento de identidad del representante diplomático.
        </div>

        <b style="display: block; margin-top: 2px;">d) Para fundaciones y/o asociaciones:</b>
        <div class="sub-inciso">
            i. Copia de Resolución de otorgación de personería jurídica.<br>
            ii. Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>

        <b style="display: block; margin-top: 2px;">e) Para sociedades civiles:</b>
        <div class="sub-inciso">
            i. Testimonio de constitución.<br>
            ii. Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>

        <b style="display: block; margin-top: 2px;">f) Para otros solicitantes:</b>
        <div class="sub-inciso">
            i. Documento que acredite capacidad jurídica para contraer obligaciones de acuerdo a su naturaleza jurídica.<br>
            ii. Certificación Electrónica del Número de Identificación Tributaria (NIT).<br>
            iii. Copia del Testimonio de Poder del representante legal y/o apoderado con facultades expresas para suscribir contratos y/o contraer obligaciones a nombre del mandante.<br>
            iv. Copia de cédula de identidad del representante legal y/o apoderado en formato digital.
        </div>
    </div>
    <div style="margin-top: 3px;">
        <b>C) Datos para la facturación:</b> Indicar el nombre del contribuyente y su NIT.
    </div>
</div>

<p class="text-justify" style="margin-top: 5px; margin-bottom: 0;">
    Asimismo, para la realización del evento deberá efectuar, <b>{{ $plazo_saldo }}</b>
</p>

{{-- PÁGINA 3 --}}
<div style="page-break-before: always;"></div>

<p class="text-justify" style="margin-top: 0; margin-bottom: 4px;">
    antes de la fecha programada para el inicio del evento:
</p>

<div class="seccion-inciso" style="margin-top: 0; margin-bottom: 4px;">
    <b>A) Servicio de arrendamiento:</b> Depósito bancario original o comprobante de transferencia electrónica del <b>{{ $porcentaje_saldo }}% del monto total cotizado</b> de arrendamiento, por concepto del <b>SALDO</b> restante a la Cuenta Corriente Fiscal del Banco Unión S.A. N° {{ $cuenta_anticipo_numero }} “{{ $cuenta_anticipo_titular }}”, consignando en la glosa el nombre de su evento, a fin de contar con el pago del 100% del costo total del servicio.
</div>

<div class="seccion-inciso" style="margin-bottom: 5px;">
    <b>B) Garantía:</b> Depósito bancario original o comprobante de transferencia electrónica correspondiente al <b>{{ $porcentaje_garantia }}% del monto total cotizado</b> de arrendamiento, por concepto de <b>GARANTÍA</b>, a la Cuenta Corriente Fiscal del Banco Unión S.A. N° {{ $cuenta_garantia_numero }} “{{ $cuenta_garantia_titular }}”, consignando en la glosa el nombre de su evento. Este pago no forma parte del anticipo señalado precedentemente.
</div>

<p class="text-justify" style="margin-bottom: 5px;">
    Por otra parte, se hace conocer que la organización deberá desarrollar su evento de acuerdo a las disposiciones establecidas en el Reglamento Operativo del Campo Ferial Chuquiago Marka, particularmente a las previsiones contenidas en el Título II – Eventos y Participación, Capítulo VII – Régimen Técnico Operativo, así como en el Título IV – Régimen Sancionatorio, Reparación del Daño y Gestión de Cobranza, en lo que resulte aplicable.
</p>

<p class="text-justify" style="margin-bottom: 3px;">
    Para la coordinación de los aspectos operativos y administrativos relacionados con el desarrollo del evento, se encuentran habilitados los siguientes canales oficiales:
</p>

<div style="margin-left: 16px; margin-bottom: 6px;">
    <b>Teléfono:</b> {{ $telefono }}.<br>
    <b>Correo electrónico:</b> <span style="text-decoration: underline; color: #0044cc;">{{ $correo_electronico }}</span>
</div>

<p class="text-justify" style="margin-bottom: 6px;">
    Sin otro particular, reitero a usted las seguridades de mi consideración y estima.
</p>

<p style="margin-bottom: 25px;">Atentamente,</p>

<div style="margin-top: 15px;">
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
