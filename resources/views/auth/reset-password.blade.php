<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email address')" required />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" required />
            <x-password-input id="password" class="mt-1.5 block w-full" name="password" required autocomplete="new-password" placeholder="{{ __('At least 8 characters') }}" />
            <x-input-error :messages="$errors->get('password')" />
            <p class="mt-1 text-xs text-slate-500">{{ __('Create a strong password with at least 8 characters.') }}</p>
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" required />
            <x-password-input id="password_confirmation" class="mt-1.5 block w-full"
                                name="password_confirmation" required autocomplete="new-password"
                                placeholder="{{ __('Repeat your new password') }}" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-submit-button :label="__('Reset password')" :loading="__('Resetting password...')" class="w-full" />
    </form>

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-500">
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">{{ __('Back to sign in') }}</a>
    </p>
</x-guest-layout>
