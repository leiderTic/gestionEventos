@extends('documentos.plantilla_base')

@section('titulo', 'Respuesta a Solicitud de Cotización')

@section('contenido')
<div style="text-align: right; margin-bottom: 25px;">
    <span>La Paz, 15 de octubre de 2026</span><br>
    <span class="bold">UCPP/DG/EXT/N° 045/2026</span>
</div>

<div style="margin-bottom: 25px;">
    Señor(a):<br>
    <span class="bold">JUAN PÉREZ RODRÍGUEZ</span><br>
    <span class="bold">GERENTE GENERAL - EVENTOS BOLIVIA S.R.L.</span><br>
    Presente.-
</div>

<div class="text-right bold underline" style="margin-bottom: 20px;">
    REF.: RESPUESTA A SOLICITUD DE COTIZACIÓN POR ARRENDAMIENTO DE ESPACIOS
</div>

<div class="text-justify" style="margin-bottom: 15px;">
    De mi mayor consideración:
</div>

<div class="text-justify" style="margin-bottom: 15px;">
    A tiempo de hacerle llegar un cordial y atento saludo, en atención a su solicitud para el arrendamiento de espacios en el Campo Ferial Chuquiago Marka, tengo a bien remitirle la cotización formal correspondiente para la realización de su evento.
</div>

<table class="table-custom">
    <thead>
        <tr>
            <th style="width: 10%;">Item</th>
            <th style="width: 45%;">Espacio / Salón</th>
            <th style="width: 25%;">Período / Fechas</th>
            <th style="width: 20%;">Total (Bs.)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">1</td>
            <td>Auditorio Illimani - Bloque Verde</td>
            <td class="text-center">20/11/2026 - 22/11/2026</td>
            <td class="text-right">15.000,00</td>
        </tr>
        <tr>
            <td class="text-center">2</td>
            <td>Sala de Exposición Temporal - Bloque Rojo</td>
            <td class="text-center">20/11/2026 - 22/11/2026</td>
            <td class="text-right">8.500,00</td>
        </tr>
        <tr>
            <td colspan="3" class="text-right bold">TOTAL GENERAL BS.</td>
            <td class="text-right bold">23.500,00</td>
        </tr>
    </tbody>
</table>

<div class="text-justify" style="margin-top: 20px;">
    Sin otro particular y esperando que la presente propuesta sea de su entera conformidad, me despido con las consideraciones más distinguidas.
</div>

<div style="text-align: center; margin-top: 60px;">
    __________________________________<br>
    <span class="bold">Lic. Dirección General Ejecutiva</span><br>
    Unidad de Coordinación de Programas y Proyectos (UCPP)
</div>
@endsection
