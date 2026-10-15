<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantillaDocumento;

class PlantillaDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        PlantillaDocumento::updateOrCreate(
            ['codigo' => 'RESPUESTA_ARRENDAMIENTO'],
            [
                'titulo' => 'Respuesta a Solicitud de Cotización por Arrendamiento de Espacios',
                'prefijo_cite' => 'MEFP/UCPP/ UCFCM/NE N°',
                'parametros_default' => [
                    'plazo_anticipo' => 'tres (3) días hábiles',
                    'porcentaje_anticipo' => 20,
                    'cuenta_anticipo_banco' => 'Banco Unión S.A.',
                    'cuenta_anticipo_numero' => '10000041434220',
                    'cuenta_anticipo_titular' => 'MEFP-UCPP-RECURSOS PROPIOS',
                    'plazo_saldo' => 'hasta diez (10) días calendario',
                    'porcentaje_saldo' => 80,
                    'porcentaje_garantia' => 20,
                    'cuenta_garantia_banco' => 'Banco Unión S.A.',
                    'cuenta_garantia_numero' => '10000041433686',
                    'cuenta_garantia_titular' => 'MEFP-UCPP-FONDO ROTATIVO',
                    'telefono' => '77240445',
                    'correo_electronico' => 'heidy.burgoa@economiayfinanzas.gob.bo',
                    'moscas' => 'RMRR/JLJC/HABB',
                ],
                'contenido_estatico' => [
                    'referencia_base' => 'RESPUESTA A SOLICITUD DE COTIZACIÓN POR ARRENDAMIENTO DE ESPACIOS',
                    'parrafo_agradecimiento' => 'Agradeciendo la consideración del Campo Ferial Chuquiago Marka para la realización de su evento, expresamos nuestra disposición para atender sus consultas y requerimientos, brindando la orientación y coordinación necesarias para la adecuada organización y desarrollo de la actividad.',
                    'normativa_reglamento' => 'Por otra parte, se hace conocer que la organización deberá desarrollar su evento de acuerdo a las disposiciones establecidas en el Reglamento Operativo del Campo Ferial Chuquiago Marka, particularmente a las previsiones contenidas en el Título II – Eventos y Participación, Capítulo VII – Régimen Técnico Operativo, así como en el Título IV – Régimen Sancionatorio, Reparación del Daño y Gestión de Cobranza, en lo que resulte aplicable.',
                    'despedida' => 'Sin otro particular, reitero a usted las seguridades de mi consideración y estima.',
                    'cargo_firmante' => 'Dirección General Ejecutiva',
                    'institucion_firmante' => 'Unidad de Coordinación de Programas y Proyectos (UCPP)',
                ],
                'activo' => true,
            ]
        );
    }
}
