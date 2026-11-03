@csrf

<div class="space-y-5">
    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <x-input-label for="code" :value="__('Code')" :required="true" />
            <x-text-input id="code" name="code" type="text" class="mt-1.5 uppercase" :value="old('code', $program->code)" placeholder="{{ __('e.g. BCA') }}" required autofocus />
            <p class="crs-help">{{ __('Short code used in dropdowns.') }}</p>
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="name" :value="__('Program name')" :required="true" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5" :value="old('name', $program->name)" placeholder="{{ __('e.g. Bachelor of Computer Application') }}" required />
            <p class="crs-help">{{ __('Full program name shown to students.') }}</p>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="crs-textarea mt-1.5" placeholder="{{ __('Optional details about this program.') }}">{{ old('description', $program->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <input type="hidden" name="is_active" value="0">

    <label for="is_active" class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50">
        <input id="is_active" name="is_active" type="checkbox" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500" @checked(old('is_active', $program->is_active ?? true)) />

        <span>
            <span class="block text-sm font-medium text-slate-900">{{ __('Active') }}</span>
            <span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ __('Only active programs appear in the registration dropdown.') }}</span>
        </span>
    </label>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <x-button-link :href="route('programs.index')" variant="secondary">
        {{ __('Cancel') }}
    </x-button-link>

    <x-submit-button :label="$program->exists ? __('Save changes') : __('Create program')" :loading="__('Saving...')" />
</div>
