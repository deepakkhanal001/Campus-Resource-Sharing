@props(['href', 'icon', 'label', 'active' => false])

<a
    href="{{ $href }}"
    @click="sidebarOpen = false"
    @if ($active) aria-current="page" @endif
    :class="collapsed ? 'lg:!justify-center lg:!px-2' : ''"
    {{ $attributes->merge([
        'class' => 'group flex items-center justify-start gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 md:justify-center md:px-2 lg:justify-start lg:px-3 '
            .($active ? 'bg-brand-500/15 text-white shadow-sm ring-1 ring-inset ring-brand-400/20' : 'text-slate-300 hover:bg-white/5 hover:text-white'),
        'aria-label' => $label,
    ]) }}
>
    <x-icon
        :name="$icon"
        class="h-5 w-5 shrink-0 {{ $active ? 'text-brand-300' : 'text-slate-400 group-hover:text-slate-200' }}"
    />

    <span class="hidden truncate max-md:inline lg:inline" :class="collapsed ? 'lg:!hidden' : ''">{{ $label }}</span>

    @if ($active)
        <span class="ms-auto hidden h-1.5 w-1.5 rounded-full bg-brand-300 max-md:block lg:block" :class="collapsed ? 'lg:!hidden' : ''"></span>
    @endif
</a>
