<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banco;
use App\Models\TipoRespaldo;
use App\Models\Porcentaje;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Bancos de Bolivia
        $bancos = [
            'Banco Unión S.A.',
            'Banco Nacional de Bolivia (BNB)',
            'Banco Mercantil Santa Cruz',
            'Banco de Crédito de Bolivia (BCP)',
            'Banco Bisa',
            'BancoSol',
            'Banco Ganadero',
            'Banco Económico',
            'Banco FIE',
            'Banco Prodem',
            'Banco de Desarrollo Productivo (BDP)',
            'Banco PyME Los Andes Procredit',
            'Banco Fortaleza',
            'Banco Regional del Oriente',
        ];
        foreach ($bancos as $banco) {
            Banco::updateOrCreate(['nombre' => $banco]);
        }

        // Tipo de Respaldos
        $tipos = [
            'Correo electrónico',
            'Carta institucional',
            'WhatsApp',
            'Orden de Compra',
            'Acta de reunión',
            'Otro documento',
        ];
        foreach ($tipos as $tipo) {
            TipoRespaldo::updateOrCreate(['descripcion' => $tipo]);
        }

        // Porcentajes
        $porcentajes = [
            ['descripcion' => 'Anticipo Reserva', 'porcentaje' => 20],
            ['descripcion' => 'Saldo Pendiente', 'porcentaje' => 80],
            ['descripcion' => 'Pago Total', 'porcentaje' => 100],
        ];
        foreach ($porcentajes as $p) {
            Porcentaje::updateOrCreate(['descripcion' => $p['descripcion']], $p);
        }
    }
}
