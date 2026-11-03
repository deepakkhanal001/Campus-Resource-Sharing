@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="resource_id" :value="__('Resource')" :required="true" />
        <select id="resource_id" name="resource_id" class="crs-select mt-1.5" required>
            <option value="">{{ __('Select a resource') }}</option>
            @foreach ($resources as $resource)
                <option value="{{ $resource->id }}" @selected(old('resource_id', $borrowingRecord->resource_id) == $resource->id)>
                    {{ $resource->title }} — {{ ucfirst($resource->availability_status) }}
                </option>
            @endforeach
        </select>
        <p class="crs-help">{{ __('Only available resources that belong to another user can be requested.') }}</p>
        <x-input-error :messages="$errors->get('resource_id')" class="mt-2" />
    </div>

    @if ($borrowingRecord->exists)
        <div>
            <x-input-label for="status" :value="__('Status')" :required="true" />
            <select id="status" name="status" class="crs-select mt-1.5" required>
                @foreach (['pending', 'approved', 'picked_up', 'returned', 'rejected', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $borrowingRecord->status) === $status)>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
            </select>
            <p class="crs-help">{{ __('Update the status as the request progresses.') }}</p>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="pickup_date" :value="__('Pickup date')" />
            <x-text-input id="pickup_date" name="pickup_date" type="date" class="mt-1.5" :value="old('pickup_date', $borrowingRecord->pickup_date?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('pickup_date')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="return_date" :value="__('Return date')" />
            <x-text-input id="return_date" name="return_date" type="date" class="mt-1.5" :value="old('return_date', $borrowingRecord->return_date?->format('Y-m-d'))" />
            <x-input-error :messages="$errors->get('return_date')" class="mt-2" />
        </div>
    </div>

    <p class="crs-help">{{ __('The return date must be the same as or later than the pickup date.') }}</p>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <x-button-link :href="route('borrowing-records.index')" variant="secondary">
        {{ __('Cancel') }}
    </x-button-link>

    <x-submit-button :label="$borrowingRecord->exists ? __('Save changes') : __('Submit request')" :loading="__('Submitting...')" />
</div>
