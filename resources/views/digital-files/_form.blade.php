@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="resource_id" :value="__('Digital resource')" :required="true" />
        <select id="resource_id" name="resource_id" class="crs-select mt-1.5" required>
            <option value="">{{ __('Select a digital resource') }}</option>
            @foreach ($resources as $resource)
                <option value="{{ $resource->id }}" @selected(old('resource_id', $selectedResourceId) == $resource->id)>
                    {{ $resource->title }}
                </option>
            @endforeach
        </select>
        <p class="crs-help">{{ __('Only digital resources can have files attached to them.') }}</p>
        <x-input-error :messages="$errors->get('resource_id')" class="mt-2" />
    </div>

    @if ($digitalFile->exists)
        <div class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-inset ring-slate-200">
                <x-icon name="document" class="h-4 w-4" />
            </span>

            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Current file') }}</p>
                <p class="mt-1 break-words text-sm font-medium text-slate-900">{{ $digitalFile->file_name }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $digitalFile->file_type }} · <x-file-size :bytes="$digitalFile->size" /></p>
            </div>
        </div>
    @endif

    <div>
        <x-input-label for="file" :value="$digitalFile->exists ? __('Replace file (optional)') : __('File')" :required="! $digitalFile->exists" />
        <input
            id="file"
            name="file"
            type="file"
            class="mt-1.5 block w-full cursor-pointer rounded-lg border border-slate-300 bg-white text-sm text-slate-600 shadow-sm transition file:me-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
            @required(! $digitalFile->exists)
        />
        <p class="crs-help">{{ $digitalFile->exists ? __('Choose a new file only if you want to replace the current one.') : __('Choose the file you want to attach to this resource.') }}</p>
        <x-input-error :messages="$errors->get('file')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <x-button-link :href="route('digital-files.index')" variant="secondary">
        {{ __('Cancel') }}
    </x-button-link>

    <x-submit-button :label="$digitalFile->exists ? __('Save changes') : __('Upload file')" :loading="__('Uploading...')" />
</div>
