@php
    $currentUser = auth()->user();
    $roleLabel = $currentUser?->getRoleNames()->first() ?? $currentUser?->role ?? 'member';

    $isAdminNav = false;
    if ($currentUser) {
        $roleNav = strtolower((string) ($currentUser->role ?? ''));
        if ($roleNav === 'admin') {
            $isAdminNav = true;
        }
        if (! $isAdminNav && method_exists($currentUser, 'hasRole')) {
            try {
                $isAdminNav = $currentUser->hasRole('admin');
            } catch (\Throwable $e) {
            }
        }
        if (! $isAdminNav && method_exists($currentUser, 'getRoleNames')) {
            try {
                $isAdminNav = in_array('admin', $currentUser->getRoleNames()->map(fn ($role) => strtolower((string) $role))->all(), true);
            } catch (\Throwable $e) {
            }
        }
    }
@endphp

<div
    x-cloak
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm md:hidden"
    @click="sidebarOpen = false"
    aria-hidden="true"
></div>

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-[17rem] -translate-x-full transform flex-col border-r border-white/5 bg-[#0b1424] text-slate-200 transition-all duration-200 ease-out md:w-20 md:translate-x-0"
    :class="[sidebarOpen ? '!translate-x-0' : '', collapsed ? 'lg:w-24' : 'lg:w-[17rem]']"
    aria-label="{{ __('Primary navigation') }}"
>
    <div
        class="flex h-[68px] shrink-0 items-center justify-start border-b border-white/[0.07] px-4 md:justify-center md:px-2 lg:justify-start lg:px-4"
        :class="collapsed ? 'lg:!justify-center lg:!px-2' : ''"
    >
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3" aria-label="{{ __('Campus Resources dashboard') }}">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-white shadow-sm ring-1 ring-inset ring-white/10">
                <x-icon name="book-open" class="h-5 w-5" />
            </span>

            <span class="hidden min-w-0 max-md:block lg:block" :class="collapsed ? 'lg:!hidden' : ''">
                <span class="block truncate font-display text-[13px] font-bold leading-tight text-white">{{ __('Campus Resources') }}</span>
                <span class="mt-0.5 block truncate text-[10px] font-medium uppercase tracking-[0.12em] text-slate-500">{{ __('Gomendra Multiple College') }}</span>
            </span>
        </a>

        <button
            type="button"
            class="ms-auto rounded-lg p-2 text-slate-400 transition hover:bg-white/5 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 md:hidden"
            @click="sidebarOpen = false"
            aria-label="{{ __('Close navigation') }}"
        >
            <x-icon name="x-mark" class="h-5 w-5" />
        </button>
    </div>

    <button
        type="button"
        class="absolute -right-3 top-[88px] hidden h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-500 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 lg:flex"
        @click="toggleSidebar"
        :aria-expanded="!collapsed"
        aria-controls="app-sidebar"
        :aria-label="collapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
        :title="collapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
    >
        <x-icon name="chevrons-left" class="h-3.5 w-3.5 transition-transform" x-bind:class="collapsed ? 'rotate-180' : ''" />
    </button>

    <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4 md:px-2 lg:px-3">
        <div>
            <p class="hidden px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 max-md:block lg:block" :class="collapsed ? 'lg:sr-only' : ''">
                {{ __('Overview') }}
            </p>

            <div class="mt-2 space-y-1">
                <x-sidebar-link :href="route('dashboard')" icon="dashboard" :label="__('Dashboard')" :active="request()->routeIs('dashboard')" />
            </div>
        </div>

        <div>
            <p class="hidden px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 max-md:block lg:block" :class="collapsed ? 'lg:sr-only' : ''">
                {{ __('Library management') }}
            </p>

            <div class="mt-2 space-y-1">
                <x-sidebar-link :href="route('resources.index')" icon="collection" :label="__('Resources')" :active="request()->routeIs('resources.*')" />
                <x-sidebar-link :href="route('categories.index')" icon="folder" :label="__('Categories')" :active="request()->routeIs('categories.*')" />
                <x-sidebar-link :href="route('digital-files.index')" icon="document" :label="__('Digital files')" :active="request()->routeIs('digital-files.*')" />
            </div>
        </div>

        <div>
            <p class="hidden px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 max-md:block lg:block" :class="collapsed ? 'lg:sr-only' : ''">
                {{ __('Requests') }}
            </p>

            <div class="mt-2 space-y-1">
                <x-sidebar-link :href="route('public.borrowings.index')" icon="arrow-up-tray" :label="__('My requests')" :active="request()->routeIs('public.borrowings.*')" />
                <x-sidebar-link :href="route('public.owner-requests.index')" icon="inbox-arrow-down" :label="__('Incoming requests')" :active="request()->routeIs('public.owner-requests.*')" />
                <x-sidebar-link :href="route('borrowing-records.index')" icon="borrowing" :label="__('Borrowing records')" :active="request()->routeIs('borrowing-records.*')" />
                <x-sidebar-link :href="route('notifications.index')" icon="bell" :label="__('Notifications')" :active="request()->routeIs('notifications.*')" />
            </div>
        </div>

        @if ($isAdminNav)
            <div>
                <p class="hidden px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 max-md:block lg:block" :class="collapsed ? 'lg:sr-only' : ''">
                    {{ __('Administration') }}
                </p>

                <div class="mt-2 space-y-1">
                    <x-sidebar-link :href="route('admin.members.index')" icon="users" :label="__('Registered members')" :active="request()->routeIs('admin.members.*')" />
                    <x-sidebar-link :href="route('programs.index')" icon="academic-cap" :label="__('Program setup')" :active="request()->routeIs('programs.*')" />
                </div>
            </div>
        @endif

        <div>
            <p class="hidden px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-500 max-md:block lg:block" :class="collapsed ? 'lg:sr-only' : ''">
                {{ __('Account') }}
            </p>

            <div class="mt-2 space-y-1">
                <x-sidebar-link :href="route('profile.edit')" icon="user-circle" :label="__('Profile settings')" :active="request()->routeIs('profile.*')" />
                <x-sidebar-link :href="route('public.resources.index')" icon="globe" :label="__('Browse library')" :active="false" />
            </div>
        </div>
    </nav>

    <div class="border-t border-white/[0.07] p-3">
        <div class="flex items-center justify-start gap-3 rounded-lg bg-white/[0.04] p-2.5 ring-1 ring-inset ring-white/[0.06] md:justify-center md:p-2 lg:justify-start lg:p-2.5" :class="collapsed ? 'lg:!justify-center lg:!p-2' : ''">
            <a
                href="{{ route('profile.edit') }}"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white ring-2 ring-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                aria-label="{{ __('View profile') }}"
                title="{{ __('View profile') }}"
            >
                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->name, 0, 1)) }}
            </a>

            <div class="hidden min-w-0 flex-1 max-md:block lg:block" :class="collapsed ? 'lg:!hidden' : ''">
                <p class="truncate text-sm font-semibold text-white">{{ $currentUser->name }}</p>
                <p class="truncate text-[11px] capitalize text-slate-400">{{ $roleLabel }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="hidden max-md:block lg:block" :class="collapsed ? 'lg:!hidden' : ''">
                @csrf
                <button
                    type="submit"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-white/5 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                    aria-label="{{ __('Log out') }}"
                    title="{{ __('Log out') }}"
                >
                    <x-icon name="logout" class="h-5 w-5" />
                </button>
            </form>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-2 hidden justify-center" :class="collapsed ? 'lg:flex' : ''">
            @csrf
            <button
                type="submit"
                class="rounded-lg p-2 text-slate-400 transition hover:bg-white/5 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                aria-label="{{ __('Log out') }}"
                title="{{ __('Log out') }}"
            >
                <x-icon name="logout" class="h-5 w-5" />
            </button>
        </form>
    </div>
</aside>
