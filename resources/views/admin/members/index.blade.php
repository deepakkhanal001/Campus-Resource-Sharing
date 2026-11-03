<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Registered Members')"
            :description="__('View the accounts registered on the platform. This list contains actual platform users and does not expose the college student registry.')"
        />
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto max-w-[1440px] space-y-6 px-4 sm:px-6 lg:px-8 2xl:max-w-screen-2xl">
            @include('partials.flash')

            <section class="crs-card" aria-labelledby="member-filters-title">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <div>
                            <h2 id="member-filters-title" class="text-base font-semibold text-slate-900">{{ __('Platform members') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ trans_choice(':count registered member|:count registered members', $members->total(), ['count' => number_format($members->total())]) }}
                            </p>
                        </div>

                        <form method="GET" action="{{ route('admin.members.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="min-w-0 sm:w-72">
                                <label for="member-search" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Search') }}</label>
                                <input
                                    id="member-search"
                                    type="search"
                                    name="q"
                                    value="{{ $filters['q'] }}"
                                    class="crs-input mt-1.5 w-full"
                                    placeholder="{{ __('Name, registration number, or email') }}"
                                >
                            </div>

                            <div class="sm:w-44">
                                <label for="member-role" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Role') }}</label>
                                <select id="member-role" name="role" class="crs-select mt-1.5 w-full">
                                    <option value="">{{ __('All roles') }}</option>
                                    <option value="student" @selected($filters['role'] === 'student')>{{ __('Students') }}</option>
                                    <option value="admin" @selected($filters['role'] === 'admin')>{{ __('Admins') }}</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                                >
                                    {{ __('Filter') }}
                                </button>

                                @if ($hasFilters)
                                    <a
                                        href="{{ route('admin.members.index') }}"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                    >
                                        {{ __('Reset') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                @if ($members->isEmpty())
                    <div class="px-5 py-12 text-center sm:px-6">
                        <x-icon name="users" class="mx-auto h-8 w-8 text-slate-400" />
                        <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('No registered members found') }}</p>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $hasFilters ? __('Try a different search term or role filter.') : __('Member accounts will appear here after registration.') }}
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th scope="col" class="px-5 py-3 sm:px-6">{{ __('Member') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Registration number') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Program') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Role') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Account status') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Registered') }}</th>
                                    <th scope="col" class="px-5 py-3 text-right sm:px-6">{{ __('Listings') }}</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($members as $member)
                                    <tr>
                                        <td class="px-5 py-4 sm:px-6">
                                            <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                                            <p class="mt-0.5 text-xs text-slate-500">{{ $member->email }}</p>
                                        </td>

                                        <td class="px-4 py-4">
                                            @if ($member->college_student_id)
                                                <span class="font-mono text-xs font-medium text-slate-800">{{ $member->college_student_id }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">{{ __('Not provided') }}</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4">
                                            <span class="text-sm text-slate-700">{{ $member->program ?: __('Not set') }}</span>
                                        </td>

                                        <td class="px-4 py-4">
                                            @if (strtolower((string) $member->role) === 'admin')
                                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-600/20">{{ __('Admin') }}</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">{{ __('Student') }}</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-4">
                                            <x-status-badge :status="$member->status" />
                                        </td>

                                        <td class="px-4 py-4">
                                            <span class="whitespace-nowrap text-sm text-slate-600">
                                                {{ \Illuminate\Support\Carbon::parse($member->created_at)->format('d M Y') }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-medium text-slate-700 sm:px-6">
                                            {{ number_format((int) $member->resources_count) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($members->hasPages())
                        <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                            {{ $members->links() }}
                        </div>
                    @endif
                @endif
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                <p class="font-semibold text-slate-900">{{ __('About this list') }}</p>
                <p class="mt-1 leading-6">
                    {{ __('Only accounts that registered on this platform are shown. The college student registry is used for registration checks and is not displayed here.') }}
                </p>
            </section>
        </div>
    </div>
</x-app-layout>
