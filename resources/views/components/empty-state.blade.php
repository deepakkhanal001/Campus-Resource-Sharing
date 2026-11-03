@props(['icon' => 'inbox', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center px-4 py-10 text-center">
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>

    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5">
            {{ $action }}
        </div>
    @endisset
</div>
