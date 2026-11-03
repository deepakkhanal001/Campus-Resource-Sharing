<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $read = (string) $request->query('read', '');

        $query = Notification::with('user')->latest('id');

        if (! auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }

        $notifications = $query
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('message', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($read === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($read === 'read', fn ($query) => $query->where('is_read', true))
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('notifications.create', [
            'notification' => new Notification(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'is_read' => ['nullable', 'boolean'],
        ]);

        Notification::create([
            ...$validated,
            'is_read' => $request->boolean('is_read'),
        ]);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    public function show(Notification $notification): View
    {
        $this->authorizeNotification($notification);

        $notification->load('user');

        return view('notifications.show', compact('notification'));
    }

    public function edit(Notification $notification): View
    {
        $this->authorizeAdmin();

        return view('notifications.edit', [
            'notification' => $notification,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'is_read' => ['nullable', 'boolean'],
        ]);

        $notification->update([
            ...$validated,
            'is_read' => $request->boolean('is_read'),
        ]);

        return redirect()
            ->route('notifications.show', $notification)
            ->with('success', 'Notification updated successfully.');
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $this->authorizeAdmin();

        $notification->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        $this->authorizeNotification($notification);

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }

    private function authorizeNotification(Notification $notification): void
    {
        abort_unless(
            auth()->user()->hasRole('admin') || $notification->user_id === auth()->id(),
            403
        );
    }
}
