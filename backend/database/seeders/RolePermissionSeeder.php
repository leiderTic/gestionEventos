<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public const ADMIN = 'Administrador';
    public const COMERCIAL = 'Tecnico Comercial';
    public const UAF = 'Tecnico UAF';
    public const LEGAL = 'Tecnico Legal';

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            'usuarios.gestionar',
            'catalogos.gestionar',
            'cotizaciones.ver_todas',
            'cotizaciones.ver_propias',
            'cotizaciones.crear',
            'cotizaciones.editar',
            'reservas.confirmar',
            'pagos.registrar',
            'informes.final',
            'uaf.ver_con_boucher',
            'uaf.subir_c21_factura',
            'legal.ver_pagadas',
            'legal.revisar',
            'contratos.gestionar',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $matriz = [
            self::ADMIN => $permisos,
            self::COMERCIAL => [
                'cotizaciones.ver_propias',
                'cotizaciones.crear',
                'cotizaciones.editar',
                'reservas.confirmar',
                'pagos.registrar',
                'informes.final',
            ],
            self::UAF => [
                'uaf.ver_con_boucher',
                'uaf.subir_c21_factura',
            ],
            self::LEGAL => [
                'legal.ver_pagadas',
                'legal.revisar',
                'contratos.gestionar',
            ],
        ];

        foreach ($matriz as $rol => $perms) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web'])->syncPermissions($perms);
        }
    }
}
