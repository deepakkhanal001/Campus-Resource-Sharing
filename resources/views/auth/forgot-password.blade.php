<x-guest-layout>
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" required />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-submit-button :label="__('Email reset link')" :loading="__('Sending link...')" class="w-full" />
    </form>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">{{ __('Back to sign in') }}</a>
    </p>
</x-guest-layout>
