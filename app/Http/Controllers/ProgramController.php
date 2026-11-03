<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $search = trim((string) $request->query('q', ''));

        $programs = Program::withCount('users')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('programs.index', compact('programs'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('programs.create', ['program' => new Program()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->normalizeCode($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:programs,code'],
            'name' => ['required', 'string', 'max:255', 'unique:programs,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Program::create($validated);

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program created successfully.');
    }

    public function edit(Program $program): View
    {
        $this->authorizeAdmin();

        return view('programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $this->authorizeAdmin();
        $this->normalizeCode($request);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('programs', 'code')->ignore($program->id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('programs', 'name')->ignore($program->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldCode = $program->code;
        $program->update($validated);

        if ($oldCode !== $program->code) {
            \App\Models\User::where('program', $oldCode)->update(['program' => $program->code]);
        }

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($program->users()->exists()) {
            return back()->with('error', 'This program is assigned to students and cannot be deleted.');
        }

        $program->delete();

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program deleted successfully.');
    }

    private function normalizeCode(Request $request): void
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }
}
