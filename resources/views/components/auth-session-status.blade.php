@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-2.5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800']) }}>
        <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" />
        <p>{{ $status }}</p>
    </div>
@endif
