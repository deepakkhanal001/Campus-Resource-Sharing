@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="user_id" :value="__('Recipient')" :required="true" />
        <select id="user_id" name="user_id" class="crs-select mt-1.5" required>
            <option value="">{{ __('Select a user') }}</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $notification->user_id) == $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="message" :value="__('Message')" :required="true" />
        <textarea id="message" name="message" rows="6" class="crs-textarea mt-1.5" placeholder="{{ __('Write a clear, useful message...') }}" required>{{ old('message', $notification->message) }}</textarea>
        <x-input-error :messages="$errors->get('message')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" :value="__('Type')" :required="true" />
        <x-text-input id="type" name="type" type="text" class="mt-1.5" :value="old('type', $notification->type)" placeholder="{{ __('e.g. general, borrowing, reminder') }}" required />
        <p class="crs-help">{{ __('A short category used to identify the notification.') }}</p>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <label for="is_read" class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition hover:bg-slate-50">
        <input id="is_read" name="is_read" type="checkbox" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500" @checked(old('is_read', $notification->is_read)) />

        <span>
            <span class="block text-sm font-medium text-slate-900">{{ __('Mark as read') }}</span>
            <span class="mt-0.5 block text-xs leading-5 text-slate-500">{{ __('The recipient will not see this as an unread notification.') }}</span>
        </span>
    </label>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-end">
    <x-button-link :href="route('notifications.index')" variant="secondary">
        {{ __('Cancel') }}
    </x-button-link>

    <x-submit-button :label="$notification->exists ? __('Save changes') : __('Create notification')" :loading="__('Saving...')" />
</div>
