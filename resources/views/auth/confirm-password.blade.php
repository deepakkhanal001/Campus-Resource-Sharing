<x-guest-layout>
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" required />
            <x-password-input id="password" class="mt-1.5 block w-full"
                name="password"
                required autocomplete="current-password"
                placeholder="{{ __('Enter your password') }}" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-submit-button :label="__('Confirm password')" :loading="__('Confirming...')" class="w-full" />
    </form>
</x-guest-layout>
