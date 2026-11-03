@props(['title', 'description' => null])

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <h1 class="break-words text-[21px] font-bold leading-tight tracking-tight text-slate-900 sm:text-[24px]">
            {{ $title }}
        </h1>

        @if ($description)
            <p class="mt-1 max-w-3xl text-[13px] leading-5 text-slate-500">
                {{ $description }}
            </p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
