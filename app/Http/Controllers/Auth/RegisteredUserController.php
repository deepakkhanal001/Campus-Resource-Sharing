<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\CollegeStudentRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PDOException;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $programs = DB::select(
            'SELECT code, name FROM programs WHERE is_active = 1 ORDER BY name ASC'
        );

        return view('auth.register', compact('programs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'college_student_id' => ['required', 'string', 'max:32'],
            'program' => ['nullable', 'string', 'max:100', 'exists:programs,code'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $name = preg_replace('/\s+/u', ' ', trim((string) $validated['name'])) ?? trim((string) $validated['name']);
        $registrationNumber = CollegeStudentRegistry::normalizeRegistrationNumber(
            (string) $validated['college_student_id']
        );
        $email = mb_strtolower(trim((string) $validated['email']), 'UTF-8');

        if (! CollegeStudentRegistry::isValidRegistrationNumber($registrationNumber)) {
            throw ValidationException::withMessages([
                'college_student_id' => __('The registration number must use the format XX-X-X-XXXXX-XXXX.'),
            ]);
        }

        $registryStudent = CollegeStudentRegistry::findByRegistrationNumber($registrationNumber);

        if (! $registryStudent) {
            throw ValidationException::withMessages([
                'college_student_id' => __('This registration number is not recognized. Please enter a valid college registration number.'),
            ]);
        }

        if (! (int) $registryStudent->is_active) {
            throw ValidationException::withMessages([
                'college_student_id' => __('This registration number is inactive in the college student registry. Contact the college administration.'),
            ]);
        }

        if (CollegeStudentRegistry::registrationNumberUsedByUser($registrationNumber)) {
            throw ValidationException::withMessages([
                'college_student_id' => __('This registration number has already been used by another account.'),
            ]);
        }

        if (CollegeStudentRegistry::emailUsedByUser($email)) {
            throw ValidationException::withMessages([
                'email' => __('This email address is already registered.'),
            ]);
        }

        if (! CollegeStudentRegistry::namesMatch($name, (string) $registryStudent->student_name)) {
            throw ValidationException::withMessages([
                'name' => __('The full name does not match the college registry record for this registration number.'),
            ]);
        }

        $now = now()->toDateTimeString();
        $pdo = DB::connection()->getPdo();

        try {
            $insert = $pdo->prepare(
                'INSERT INTO users
                    (name, email, college_student_id, program, password, role, status, created_at, updated_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $insert->execute([
                $name,
                $email,
                $registrationNumber,
                $registryStudent->program,
                Hash::make((string) $validated['password']),
                'student',
                'active',
                $now,
                $now,
            ]);

            $userId = (int) $pdo->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                if (CollegeStudentRegistry::emailUsedByUser($email)) {
                    throw ValidationException::withMessages([
                        'email' => __('This email address is already registered.'),
                    ]);
                }

                if (CollegeStudentRegistry::registrationNumberUsedByUser($registrationNumber)) {
                    throw ValidationException::withMessages([
                        'college_student_id' => __('This registration number has already been used by another account.'),
                    ]);
                }
            }

            report($e);

            throw ValidationException::withMessages([
                'registration' => __('Registration failed. Please try again.'),
            ]);
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'registration' => __('Registration failed. Please try again.'),
            ]);
        }

        $this->assignStudentRole($userId);

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', __('Your account was created. Your registration number matched the active college registry, so you can use student features immediately.'));
    }

    private function assignStudentRole(int $userId): void
    {
        try {
            $pdo = DB::connection()->getPdo();
            $role = $pdo->prepare('SELECT id FROM roles WHERE name = ? AND guard_name = ? LIMIT 1');
            $role->execute(['student', 'web']);
            $roleRow = $role->fetch();

            if (! $roleRow) {
                return;
            }

            $exists = $pdo->prepare(
                'SELECT 1 FROM model_has_roles WHERE role_id = ? AND model_type = ? AND model_id = ? LIMIT 1'
            );
            $exists->execute([(int) $roleRow['id'], 'App\\Models\\User', $userId]);

            if ($exists->fetch()) {
                return;
            }

            $attach = $pdo->prepare(
                'INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, ?, ?)'
            );
            $attach->execute([(int) $roleRow['id'], 'App\\Models\\User', $userId]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
