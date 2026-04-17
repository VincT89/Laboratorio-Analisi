<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'is_active' => false
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_active_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'is_active' => true
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_active_user_gets_logged_out_if_deactivated()
    {
        $user = User::factory()->create(['password' => bcrypt('password'), 'is_active' => true]);

        $this->actingAs($user);
        
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);

        // Disattivazione utente dal database (da parte admin)
        $user->update(['is_active' => false]);
        
        // Simulo ricarica model dalla sessione reale passandogli l'istanza fresh
        $this->actingAs($user->fresh());

        // Alla successiva richiesta web il middleware logga fuori
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        
        $this->assertGuest();
    }
}
