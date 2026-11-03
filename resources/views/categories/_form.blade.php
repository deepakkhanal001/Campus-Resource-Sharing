@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="name" :value="__('Name')" :required="true" />
        <x-text-input id="name" name="name" type="text" class="mt-1.5" :value="old('name', $category->name)" placeholder="{{ __('e.g. Textbooks, Lab equipment') }}" required autofocus />
        <p class="crs-help">{{ __('Use a short, descriptive name that is easy to recognize.') }}</p>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="5" class="crs-textarea mt-1.5" placeholder="{{ __('Describe what belongs in this category (optional).') }}">{{ old('description', $category->description) }}</textarea>
        <p class="crs-help">{{ __('This description helps students understand what they can find here.') }}</p>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <x-button-link :href="route('categories.index')" variant="secondary">
        {{ __('Cancel') }}
    </x-button-link>

    <x-submit-button :label="$category->exists ? __('Save changes') : __('Create category')" :loading="__('Saving...')" />
</div>
