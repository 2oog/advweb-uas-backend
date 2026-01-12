<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
             ->getJson('/api/admin/sales')
             ->assertStatus(200);
    }

    public function test_employee_cannot_access_dashboard()
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee, 'sanctum')
             ->getJson('/api/admin/sales')
             ->assertStatus(403);
    }

    public function test_admin_can_create_menu_item()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
             ->postJson('/api/menu-items', [
                 'name' => 'New Item',
                 'price' => 100,
                 // Add other required fields if any, based on MenuItem model.
                 // Assuming minimal for now or validation might fail.
                 // Let's check MenuItem factory/migration in next steps if needed.
                 // For now, assuming basic structure.
                 'category' => 'food',
                 'description' => 'Delicious' 
             ])
             ->assertStatus(201); // Or 422 if validation fails, but let's hope for 201 or 422 implies access granted at least.
             // Actually, 403 checks are what we care about first.
    }

    public function test_employee_cannot_create_menu_item()
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->actingAs($employee, 'sanctum')
             ->postJson('/api/menu-items', [
                 'name' => 'Forbidden Item',
             ])
             ->assertStatus(403);
    }
}
