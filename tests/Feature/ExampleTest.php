<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A raiz "/" é o dashboard, que exige login: visitante vai para /login.
     */
    public function test_visitante_na_raiz_e_redirecionado_para_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_tela_de_login_e_exibida(): void
    {
        $this->get(route('login'))->assertOk();
    }
}
