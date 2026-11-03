<x-guest-layout>
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" required />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" required />

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-brand-700 underline-offset-2 hover:text-brand-900 hover:underline" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-password-input id="password" class="mt-1.5 block w-full"
                name="password"
                required autocomplete="current-password"
                placeholder="{{ __('Enter your password') }}" />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="flex cursor-pointer items-center gap-2.5">
            <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
            <span class="text-sm text-slate-600">{{ __('Keep me signed in') }}</span>
        </label>

        <x-submit-button :label="__('Sign in')" :loading="__('Signing in...')" class="w-full" />
    </form>

    @if (Route::has('register'))
        <p class="mt-5 border-t border-slate-200 pt-4 text-center text-sm text-slate-500">
            {{ __('New to the resource desk?') }}
            <a href="{{ route('register') }}" class="font-medium text-brand-700 underline-offset-2 hover:text-brand-900 hover:underline">{{ __('Create a student account') }}</a>
        </p>
    @endif
</x-guest-layout>
