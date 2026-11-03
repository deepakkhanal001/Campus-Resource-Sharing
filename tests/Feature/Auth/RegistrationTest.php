<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_an_active_registry_number(): void
    {
        DB::table('programs')->insert([
            'code' => 'BCA',
            'name' => 'Bachelor of Computer Application',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('college_student_registry')->insert([
            'registration_number' => '12-3-4-56789-1234',
            'student_name' => 'Test Student',
            'program' => 'BCA',
            'academic_year' => '2081/2082',
            'is_active' => true,
            'is_test_data' => true,
            'created_at' => now(),
        ]);

        $response = $this->post('/register', [
            'name' => 'Test Student',
            'college_student_id' => '12-3-4-56789-1234',
            'program' => 'BCA',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'college_student_id' => '12-3-4-56789-1234',
            'status' => 'active',
            'role' => 'student',
        ]);
    }
}
