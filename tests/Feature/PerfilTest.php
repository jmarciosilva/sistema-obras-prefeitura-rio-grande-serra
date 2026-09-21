<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    public static function perfis(): array
    {
        return [['admin'], ['secretario'], ['operador'], ['tecnico']];
    }

    #[DataProvider('perfis')]
    public function test_qualquer_perfil_altera_nome_e_email(string $perfil): void
    {
        $user = User::factory()->create(['perfil' => $perfil, 'ativo' => true]);

        $this->actingAs($user)->from('/')
            ->put(route('perfil.update'), ['name' => 'Novo Nome', 'email' => 'novo@exemplo.com'])
            ->assertRedirect('/')
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Novo Nome', $user->name);
        $this->assertSame('novo@exemplo.com', $user->email);
        $this->assertSame($perfil, $user->perfil);
    }

    public function test_nao_permite_email_de_outro_usuario_nem_alterar_perfil(): void
    {
        User::factory()->create(['email' => 'ocupado@exemplo.com']);
        $user = User::factory()->create(['perfil' => 'tecnico']);

        $this->actingAs($user)->from('/')
            ->put(route('perfil.update'), ['name' => 'X', 'email' => 'ocupado@exemplo.com', 'perfil' => 'admin'])
            ->assertSessionHasErrorsIn('perfilDados', 'email');

        $this->actingAs($user)->from('/')
            ->put(route('perfil.update'), ['name' => 'X', 'email' => $user->email, 'perfil' => 'admin']);

        $this->assertSame('tecnico', $user->fresh()->perfil);
    }

    public function test_altera_senha_exigindo_senha_atual(): void
    {
        $user = User::factory()->create(['perfil' => 'operador']);

        $this->actingAs($user)->from('/')
            ->put(route('perfil.update-senha'), [
                'senha_atual' => 'errada',
                'password' => 'novaSenha123',
                'password_confirmation' => 'novaSenha123',
            ])
            ->assertSessionHasErrorsIn('perfilSenha', 'senha_atual');

        $this->actingAs($user)->from('/')
            ->put(route('perfil.update-senha'), [
                'senha_atual' => 'password',
                'password' => 'novaSenha123',
                'password_confirmation' => 'novaSenha123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('novaSenha123', $user->fresh()->password));
    }

    public function test_modal_renderiza_no_layout(): void
    {
        $user = User::factory()->create(['perfil' => 'secretario', 'ativo' => true]);

        $this->actingAs($user)->get(route('manual'))
            ->assertOk()
            ->assertSee("open-modal', 'meu-perfil'", false)
            ->assertSee(route('perfil.update-senha'), false);
    }
}
