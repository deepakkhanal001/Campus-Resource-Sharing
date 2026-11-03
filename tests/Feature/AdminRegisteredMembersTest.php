<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRegisteredMembersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_registered_platform_members_only(): void
    {
        $admin = $this->makeAccount('admin', 'Site Administrator', 'admin@example.com');
        $student = $this->makeAccount('student', 'Registered Student', 'student@example.com', '12-3-4-56789-1234');

        DB::table('college_student_registry')->insert([
            'registration_number' => '77-7-7-77777-7777',
            'student_name' => 'Registry Only Person',
            'program' => 'BCA',
            'academic_year' => '2081/2082',
            'is_active' => true,
            'is_test_data' => true,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.members.index'))
            ->assertOk()
            ->assertSee('Registered Members')
            ->assertSee($student->name)
            ->assertSee($student->email)
            ->assertSee('12-3-4-56789-1234')
            ->assertDontSee('Registry Only Person')
            ->assertDontSee('77-7-7-77777-7777');
    }

    public function test_admin_can_search_members_by_name_registration_number_and_email(): void
    {
        $admin = $this->makeAccount('admin', 'Site Administrator', 'admin@example.com');
        $alpha = $this->makeAccount('student', 'Alpha Member', 'alpha@example.com', '12-3-4-56789-1234');
        $beta = $this->makeAccount('student', 'Beta Member', 'beta@example.com', '12-3-4-56789-5678');

        $this->actingAs($admin)
            ->get(route('admin.members.index', ['q' => 'Alpha']))
            ->assertOk()
            ->assertSee($alpha->name)
            ->assertDontSee($beta->name);

        $this->actingAs($admin)
            ->get(route('admin.members.index', ['q' => '12-3-4-56789-5678']))
            ->assertOk()
            ->assertSee($beta->name)
            ->assertDontSee($alpha->name);

        $this->actingAs($admin)
            ->get(route('admin.members.index', ['q' => 'alpha@example.com']))
            ->assertOk()
            ->assertSee($alpha->name)
            ->assertDontSee($beta->name);
    }

    public function test_admin_can_filter_members_by_role(): void
    {
        $admin = $this->makeAccount('admin', 'Site Administrator', 'admin@example.com');
        $student = $this->makeAccount('student', 'Student Member', 'student@example.com', '12-3-4-56789-1234');

        $this->actingAs($admin)
            ->get(route('admin.members.index', ['role' => 'student']))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('1 registered member');

        $this->actingAs($admin)
            ->get(route('admin.members.index', ['role' => 'admin']))
            ->assertOk()
            ->assertSee('Site Administrator')
            ->assertSee('1 registered member')
            ->assertDontSee('Student Member');
    }

    public function test_non_admin_cannot_view_registered_members(): void
    {
        $student = $this->makeAccount('student', 'Student Member', 'student@example.com', '12-3-4-56789-1234');

        $this->actingAs($student)
            ->get(route('admin.members.index'))
            ->assertForbidden();
    }

    private function makeAccount(
        string $role,
        string $name,
        string $email,
        ?string $registrationNumber = null
    ): User {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'role' => $role,
            'status' => 'active',
            'college_student_id' => $registrationNumber,
        ]);

        return $user->fresh();
    }
}
