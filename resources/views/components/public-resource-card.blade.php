@props(['resource'])

@php
    $isDigital = $resource->type === 'digital';
    $availabilityLabel = match ($resource->availability_status) {
        'available' => __('Available'),
        'borrowed' => __('Borrowed'),
        default => __('Unavailable'),
    };
    $createdAt = $resource->created_at
        ? \Illuminate\Support\Carbon::parse($resource->created_at)->format('d M Y')
        : null;
    $ownerInitial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($resource->owner_name ?? 'U', 0, 1));
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card transition duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift">
    <a href="{{ route('public.resources.show', $resource->id) }}" class="absolute inset-0 z-10 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
        <span class="sr-only">{{ __('View :title', ['title' => $resource->title]) }}</span>
    </a>

    <div class="flex items-start justify-between gap-3 px-4 pt-4">
        <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $isDigital ? 'bg-sky-50 text-sky-600 ring-sky-100' : 'bg-brand-50 text-brand-600 ring-brand-100' }} ring-1 ring-inset">
            <x-icon :name="$isDigital ? 'document' : 'collection'" class="h-5 w-5" />
        </span>

        <x-status-badge :status="$resource->availability_status" :label="$availabilityLabel" />
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-600">{{ $resource->category_name }}</span>
            <span>{{ $isDigital ? __('Digital') : __('Physical') }}</span>
        </div>

        <h3 class="mt-2.5 line-clamp-2 text-[15px] font-semibold leading-5 text-slate-900 transition group-hover:text-brand-700">
            {{ $resource->title }}
        </h3>

        <p class="mt-1.5 line-clamp-2 text-[13px] leading-5 text-slate-500">
            {{ $resource->description ?: __('No description provided.') }}
        </p>

        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
            <div class="flex min-w-0 items-center gap-2">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600">
                    {{ $ownerInitial }}
                </span>
                <span class="truncate text-xs font-medium text-slate-600">{{ $resource->owner_name }}</span>
            </div>

            @if ($createdAt)
                <span class="shrink-0 text-xs text-slate-400">{{ $createdAt }}</span>
            @endif
        </div>
    </div>
</article>
