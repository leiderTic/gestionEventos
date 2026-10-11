<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modalidad;
use App\Models\TipoCrm;

class CrmReunionSeeder extends Seeder
{
    public function run(): void
    {
        // Modalidades de Reunión (Art. 41)
        $modalidades = [
            'Presencial',
            'Virtual (Zoom / Meet / Teams)',
            'Llamada telefónica'
        ];
        foreach ($modalidades as $m) {
            Modalidad::updateOrCreate(['descripcion' => $m]);
        }

        // Tipos de CRM / Seguimiento Comercial
        $tipos = [
            'Llamada',
            'Reunión',
            'Correo electrónico',
            'WhatsApp',
            'Visita guiada'
        ];
        foreach ($tipos as $t) {
            TipoCrm::updateOrCreate(['descripcion' => $t]);
        }
    }
}
