<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_via_web()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_redirected_to_dashboard_after_login()
    {
        $admin = User::factory()->create([
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_can_register_via_web()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertAuthenticated();
    }

    public function test_user_can_logout_via_web()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
