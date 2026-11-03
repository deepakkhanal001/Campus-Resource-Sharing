<section aria-labelledby="profile-information-title">
    <header>
        <h2 id="profile-information-title" class="text-base font-semibold text-slate-900">
            {{ __('Profile information') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ __("Update your account's name, program, and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" :required="true" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="program" :value="__('Program')" />
            <select id="program" name="program" class="crs-select mt-1.5">
                <option value="">{{ __('Select your program') }}</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->code }}" @selected(old('program', $user->program) === $program->code)>
                        {{ $program->code }} - {{ $program->name }}
                    </option>
                @endforeach
                @if ($user->program && ! $programs->contains('code', $user->program))
                    <option value="{{ $user->program }}" selected>{{ $user->program }} - {{ __('Inactive or removed program') }}</option>
                @endif
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('program')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" :required="true" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <p class="text-sm text-amber-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="font-semibold underline underline-offset-2 hover:text-amber-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                            {{ __('Send a new verification link.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 border-t border-slate-200 pt-5">
            <x-submit-button :label="__('Save changes')" :loading="__('Saving...')" />

            @if (session('status') === 'profile-updated')
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
