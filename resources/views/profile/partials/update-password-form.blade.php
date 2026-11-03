<section aria-labelledby="update-password-title">
    <header>
        <h2 id="update-password-title" class="text-base font-semibold text-slate-900">
            {{ __('Update password') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __('Use a long, random password to keep your account secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current password')" />
            <x-password-input id="update_password_current_password" name="current_password" class="mt-1.5" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New password')" />
            <x-password-input id="update_password_password" name="password" class="mt-1.5" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm password')" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" class="mt-1.5" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-4 border-t border-slate-200 pt-5">
            <x-submit-button :label="__('Update password')" :loading="__('Updating...')" />

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-medium text-emerald-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
