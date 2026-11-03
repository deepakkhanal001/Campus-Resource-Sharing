<x-guest-layout>
    <p class="mb-5 text-sm leading-6 text-slate-600">{{ __('We sent a verification link to your email address. Open it to activate your account. If it did not arrive, you can request another one below.') }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 flex items-start gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">
            <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
            <p>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <x-submit-button :label="__('Resend verification email')" :loading="__('Sending email...')" class="w-full" />
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2">
            {{ __('Sign out') }}
        </button>
    </form>
</x-guest-layout>
