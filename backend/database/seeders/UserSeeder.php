<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Usuarios mínimos de prueba (uno o más por rol).
     */
    public function run(): void
    {
        $A = RolePermissionSeeder::ADMIN;
        $C = RolePermissionSeeder::COMERCIAL;
        $U = RolePermissionSeeder::UAF;
        $L = RolePermissionSeeder::LEGAL;

        $users = [
            ["name" => "Administrador Sistema", "username" => "admin", "password" => "Admin2026!", "roles" => [$A]],
            ["name" => "Leider Ticlla",         "password" => "LT2026!", "roles" => [$A]],
            ["name" => "Justo Colquehuanca",    "password" => "JC2026!", "roles" => [$C]],
            ["name" => "Patricia Castillo",     "password" => "PC2026!", "roles" => [$C]],
            ["name" => "Diego Condori",         "password" => "DC2026!", "roles" => [$C]],
            ["name" => "Valentina Choque",      "password" => "VC2026!", "roles" => [$U]],
            ["name" => "Santiago Vargas",       "password" => "SV2026!", "roles" => [$U]],
            ["name" => "Valeria Flores",        "password" => "VF2026!", "roles" => [$L]],
            ["name" => "Alejandro Gutierrez",   "password" => "AG2026!", "roles" => [$L]],
            ["name" => "Gabriela Rojas",        "password" => "GR2026!", "roles" => [$C, $U]],
        ];

        foreach ($users as $data) {
            $username = $data["username"] ?? Str::slug($data["name"], ".");
            $parts = explode(" ", $data["name"]);
            $alias = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

            $user = User::withTrashed()->updateOrCreate(
                ["username" => $username],
                [
                    "name" => $data["name"],
                    "alias" => $alias,
                    "email" => $username . "@it.com",
                    "password" => Hash::make($data["password"]),
                    "email_verified_at" => now(),
                    "activo" => true,
                ]
            );

            $user->syncRoles($data["roles"]);
        }
    }
}
