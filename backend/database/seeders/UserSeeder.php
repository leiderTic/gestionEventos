<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ["name" => "Justo Colquehuanca", "password" => "JC2026!"],
            ["name" => "Leider Ticlla", "password" => "LT2026!"],
            ["name" => "Diego Condori", "password" => "DC2026!"],
            ["name" => "Valentina Choque", "password" => "VC2026!"],
            ["name" => "Santiago Vargas", "password" => "SV2026!"],
            ["name" => "Valeria Flores", "password" => "VF2026!"],
            ["name" => "Alejandro Gutierrez", "password" => "AG2026!"],
            ["name" => "Gabriela Rojas", "password" => "GR2026!"],
            ["name" => "Lucas Mendoza", "password" => "LM2026!"],
            ["name" => "Sofia Arce", "password" => "SA2026!"],
            ["name" => "Nicolas Torrez", "password" => "NT2026!"],
            ["name" => "Mariana Camacho", "password" => "MC2026!"],
            ["name" => "Sebastian Claure", "password" => "SC2026!"],
            ["name" => "Luciana Alarcon", "password" => "LA2026!"],
            ["name" => "Andres Zeballos", "password" => "AZ2026!"],
            ["name" => "Daniela Poma", "password" => "DP2026!"],
            ["name" => "Joaquin Apaza", "password" => "JA2026!"],
            ["name" => "Natalia Guzman", "password" => "NG2026!"],
            ["name" => "Rodrigo Vaca", "password" => "RV2026!"],
            ["name" => "Fernanda Roca", "password" => "FR2026!"],
        ];

        foreach ($users as $userData) {
            $username = Str::slug($userData["name"], ".");
            $email = $username . "@it.com"; 
            $parts = explode(" ", $userData["name"]);
            $alias = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));

            User::updateOrCreate(
                ["email" => $email],
                [
                    "name" => $userData["name"],
                    "alias" => $alias,
                    "username" => $username,
                    "password" => Hash::make($userData["password"]),
                    "email_verified_at" => now(),
                ]
            );
        }
    }
}
