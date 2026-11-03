<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="name" :value="__('Full name')" required />
                <x-text-input id="name" class="mt-1.5 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="{{ __('Your full name') }}" aria-describedby="name-help" />
                <x-input-error :messages="$errors->get('name')" />
                <p id="name-help" class="crs-help">{{ __('Use the name on your college records.') }}</p>
            </div>

            <div>
                <x-input-label for="college_student_id" :value="__('Registration number')" required />
                <x-text-input
                    id="college_student_id"
                    class="mt-1.5 block w-full font-mono"
                    type="text"
                    name="college_student_id"
                    :value="old('college_student_id')"
                    required
                    autocomplete="off"
                    maxlength="32"
                    pattern="\d{2}-\d-\d-\d{5}-\d{4}"
                    placeholder="12-3-4-56789-1234"
                    aria-describedby="college_student_id-help"
                />
                <x-input-error :messages="$errors->get('college_student_id')" />
                <p id="college_student_id-help" class="crs-help">{{ __('Format: XX-X-X-XXXXX-XXXX.') }}</p>
            </div>
        </div>

        <div>
            <x-input-label for="program" :value="__('Program (optional)')" />
            <select id="program" name="program" class="crs-select mt-1.5">
                <option value="">{{ __('Select your program') }}</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->code }}" @selected(old('program') === $program->code)>
                        {{ $program->code }} - {{ $program->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('program')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" required />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="password" :value="__('Password')" required />
                <x-password-input id="password" class="mt-1.5 block w-full"
                    name="password"
                    required autocomplete="new-password"
                    placeholder="{{ __('At least 8 characters') }}"
                    aria-describedby="password-help" />
                <x-input-error :messages="$errors->get('password')" />
                <p id="password-help" class="crs-help">{{ __('Use at least 8 characters.') }}</p>
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" required />
                <x-password-input id="password_confirmation" class="mt-1.5 block w-full"
                    name="password_confirmation" required autocomplete="new-password"
                    placeholder="{{ __('Repeat your password') }}" />
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <x-submit-button :label="__('Create student account')" :loading="__('Creating account...')" class="w-full" />
    </form>

    <p class="mt-5 border-t border-slate-200 pt-4 text-center text-sm text-slate-500">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="font-medium text-brand-700 underline-offset-2 hover:text-brand-900 hover:underline">{{ __('Sign in') }}</a>
    </p>
</x-guest-layout>
