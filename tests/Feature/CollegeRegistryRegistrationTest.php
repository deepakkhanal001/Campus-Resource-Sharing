<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollegeRegistryRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_a_malformed_registration_number(): void
    {
        $this->post('/register', $this->registrationPayload([
            'college_student_id' => '123-4-56789-1234',
        ]))->assertSessionHasErrors('college_student_id');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_unrecognized_registration_number(): void
    {
        $this->post('/register', $this->registrationPayload([
            'college_student_id' => '11-1-1-11111-1111',
        ]))->assertSessionHasErrors([
            'college_student_id' => 'This registration number is not recognized. Please enter a valid college registration number.',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_an_inactive_registry_record(): void
    {
        $this->insertRegistry('33-3-3-33333-3333', 'Dummy Inactive Student', isActive: false);

        $this->post('/register', $this->registrationPayload([
            'college_student_id' => '33-3-3-33333-3333',
            'name' => 'Dummy Inactive Student',
        ]))->assertSessionHasErrors('college_student_id');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_a_registration_number_already_used(): void
    {
        $this->insertRegistry('12-3-4-56789-1234', 'Dummy Student Alpha');
        $this->makeAccount('student', '12-3-4-56789-1234', 'existing@example.com');

        $this->post('/register', $this->registrationPayload())->assertSessionHasErrors('college_student_id');

        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        $this->insertRegistry('12-3-4-56789-1234', 'Dummy Student Alpha');
        $this->makeAccount('student', null, 'student@example.com');

        $this->post('/register', $this->registrationPayload([
            'email' => 'student@example.com',
        ]))->assertSessionHasErrors('email');

        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_registration_rejects_a_name_that_does_not_match_the_registry(): void
    {
        $this->insertRegistry('12-3-4-56789-1234', 'Dummy Student Alpha');

        $this->post('/register', $this->registrationPayload([
            'name' => 'Someone Else',
        ]))->assertSessionHasErrors([
            'name' => 'The full name does not match the college registry record for this registration number.',
        ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_matches_names_ignoring_case_and_extra_whitespace(): void
    {
        $this->insertRegistry('12-3-4-56789-1234', 'Dummy Student Alpha');

        $response = $this->post('/register', $this->registrationPayload([
            'name' => '  dummy   student   alpha  ',
        ]));

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'college_student_id' => '12-3-4-56789-1234',
            'status' => 'active',
            'role' => 'student',
            'program' => 'BCA',
        ]);

        $student = User::where('email', 'student@example.com')->firstOrFail();

        $this->actingAs($student)->get(route('resources.index'))->assertOk();
        $this->actingAs($student)->get(route('public.borrowings.index'))->assertOk();
        $this->actingAs($student)->get(route('dashboard'))->assertOk();
    }

    public function test_student_verification_admin_workflow_is_not_available(): void
    {
        $admin = $this->makeAccount('admin', null, 'admin@example.com');

        $this->actingAs($admin)
            ->get('/administration/student-verifications')
            ->assertNotFound();
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Dummy Student Alpha',
            'college_student_id' => '12-3-4-56789-1234',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }

    private function insertRegistry(
        string $registrationNumber,
        string $studentName,
        string $program = 'BCA',
        bool $isActive = true
    ): void {
        DB::table('college_student_registry')->updateOrInsert(
            ['registration_number' => $registrationNumber],
            [
                'student_name' => $studentName,
                'program' => $program,
                'academic_year' => '2081/2082',
                'is_active' => $isActive,
                'is_test_data' => true,
                'created_at' => now(),
            ]
        );
    }

    private function makeAccount(
        string $role = 'student',
        ?string $registrationNumber = null,
        ?string $email = null
    ): User {
        $user = User::factory()->create($email ? ['email' => $email] : []);

        DB::table('users')->where('id', $user->id)->update([
            'role' => $role,
            'status' => 'active',
            'college_student_id' => $registrationNumber,
        ]);

        return $user->fresh();
    }
}
