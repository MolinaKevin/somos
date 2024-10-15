<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test for login.
     *
     * @return void
     */
    public function testLogin()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'admin@example.com') // Ajusta según tus datos
                    ->type('password', 'password')
                    ->press('Login') // Ajusta si el botón tiene otro texto
                    ->assertPathIs('/dashboard'); // Asegúrate de que redirija al lugar correcto
        });
    }
}

