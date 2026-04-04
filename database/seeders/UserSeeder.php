<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [

            [
                'name'     => 'Administrador',
                'email'    => 'jmarciosilva@gmail.com',
                'password' => Hash::make('12345678'),
                'perfil'   => 'admin',
                'ativo'    => true,
                'telefone' => '(11) 4820-0000',
                'ramal'    => '101',
                'whatsapp' => '(11) 99999-9999',
            ],
            [
                'name'     => 'Técnico de Obras',
                'email'    => 'tecnico@riogrande.sp.gov.br',
                'password' => Hash::make('Tecnico@2024!'),
                'perfil'   => 'tecnico',
                'ativo'    => true,
                'telefone' => '(11) 4820-0000',
                'ramal'    => '202',
                'whatsapp' => '(11) 98888-8888',
            ],

            [
                'name'     => 'Operador',
                'email'    => 'operador@riogrande.sp.gov.br',
                'password' => Hash::make('Operador@2024!'),
                'perfil'   => 'operador',
                'ativo'    => true,
                'telefone' => '(11) 4820-0000',
                'ramal'    => '303',
                'whatsapp' => '(11) 97777-7777',
            ],
        ];

        foreach ($usuarios as $dados) {
            User::updateOrCreate(
                ['email' => $dados['email']],
                $dados
            );
        }

        $this->command->info('✅ Usuários criados: admin, técnico e operador.');
    }
}
