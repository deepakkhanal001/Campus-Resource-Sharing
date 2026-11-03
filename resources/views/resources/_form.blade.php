@php
    $resource = $resource ?? null;
    $isEdit = $resource !== null;
@endphp
@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="title" :value="__('Title')" :required="true" />
        <x-text-input id="title" name="title" type="text" class="mt-1.5" :value="old('title', $resource->title ?? '')" placeholder="{{ __('e.g. Introduction to Algorithms, 4th edition') }}" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="5" class="crs-textarea mt-1.5" placeholder="{{ __('Describe the resource, its condition, and anything a borrower should know.') }}">{{ old('description', $resource->description ?? '') }}</textarea>
        <p class="crs-help">{{ __('Include useful details such as edition, model, condition, or access notes.') }}</p>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="type" :value="__('Type')" :required="true" />
            <select id="type" name="type" class="crs-select mt-1.5" required>
                <option value="physical" @selected(old('type', $resource->type ?? 'physical') === 'physical')>{{ __('Physical') }}</option>
                <option value="digital" @selected(old('type', $resource->type ?? null) === 'digital')>{{ __('Digital') }}</option>
            </select>
            <p class="crs-help">{{ __('Digital resources can have files attached after saving.') }}</p>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="category_id" :value="__('Category')" :required="true" />
            <select id="category_id" name="category_id" class="crs-select mt-1.5" required>
                <option value="">{{ __('Select a category') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $resource->category_id ?? null) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="availability_status" :value="__('Availability')" :required="true" />
        <select id="availability_status" name="availability_status" class="crs-select mt-1.5" required>
            <option value="available" @selected(old('availability_status', $resource->availability_status ?? 'available') === 'available')>{{ __('Available') }}</option>
            <option value="unavailable" @selected(old('availability_status', $resource->availability_status ?? null) === 'unavailable')>{{ __('Unavailable') }}</option>
            <option value="borrowed" @selected(old('availability_status', $resource->availability_status ?? null) === 'borrowed')>{{ __('Borrowed') }}</option>
        </select>
        <p class="crs-help">{{ __('Keep this status current so students know whether the resource can be requested.') }}</p>
        <x-input-error :messages="$errors->get('availability_status')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <x-button-link :href="route('resources.index')" variant="secondary">
        {{ __('Cancel') }}
    </x-button-link>

    <x-submit-button :label="$isEdit ? __('Save changes') : __('Create resource')" :loading="__('Saving...')" />
</div>
