<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Request') }}: {{ $requestForm->request_no }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('requests.show', $requestForm->id) }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-sm">
                    {{ __('View') }}
                </a>
                <a href="{{ route('admin.requests') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-sm">
                    {{ __('Back to list') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 dark:bg-red-900/30 border border-red-400 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('User') }}: <x-user-label :user="$requestForm->user" /> · {{ __('Department') }}: {{ $requestForm->department->name ?? '-' }}
                    </p>

                    <form method="POST" action="{{ route('admin.requests.update', $requestForm->id) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required
                                :value="old('title', $requestForm->title)" />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                rows="3">{{ old('description', $requestForm->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="pending_chief" {{ old('status', $requestForm->status) === 'pending_chief' ? 'selected' : '' }}>{{ __('pending_chief') }}</option>
                                <option value="pending_manager" {{ old('status', $requestForm->status) === 'pending_manager' ? 'selected' : '' }}>{{ __('pending_manager') }}</option>
                                <option value="pending_purchasing" {{ old('status', $requestForm->status) === 'pending_purchasing' ? 'selected' : '' }}>{{ __('pending_purchasing') }}</option>
                                <option value="approved" {{ old('status', $requestForm->status) === 'approved' ? 'selected' : '' }}>{{ __('approved') }}</option>
                                <option value="rejected" {{ old('status', $requestForm->status) === 'rejected' ? 'selected' : '' }}>{{ __('rejected') }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div id="rejection-reason-wrap">
                            <x-input-label for="rejection_reason" :value="__('Rejection Reason')" />
                            <textarea id="rejection_reason" name="rejection_reason"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                rows="2">{{ old('rejection_reason', $requestForm->rejection_reason) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('rejection_reason')" />
                        </div>

                        <div class="border-t dark:border-gray-600 pt-4">
                            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">{{ __('Items') }}</h3>
                            <div id="items-container" class="space-y-3 mt-2">
                                @foreach($requestForm->items as $idx => $item)
                                    <div class="flex gap-2 items-start flex-wrap" data-row="existing">
                                        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}" />
                                        <div class="flex-1 min-w-[200px]">
                                            <input type="text" name="items[{{ $idx }}][content]" value="{{ old('items.'.$idx.'.content', $item->content) }}"
                                                placeholder="{{ __('Item Content') }}"
                                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                                        </div>
                                        <div class="min-w-[110px]">
                                            <select name="items[{{ $idx }}][unit_id]" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                                <option value="">{{ __('None') }}</option>
                                                @foreach($units as $u)
                                                    <option value="{{ $u->id }}" {{ old('items.'.$idx.'.unit_id', $item->unit_id) == $u->id ? 'selected' : '' }}>{{ $u->symbol ? $u->name . ' (' . $u->symbol . ')' : $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="min-w-[80px]">
                                            <input type="number" step="any" min="0" name="items[{{ $idx }}][quantity]" value="{{ old('items.'.$idx.'.quantity', $item->quantity) }}"
                                                placeholder="0" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
                                        </div>
                                        <div class="flex-1 min-w-[180px]">
                                            <input type="text" name="items[{{ $idx }}][link]" value="{{ old('items.'.$idx.'.link', $item->link) }}"
                                                placeholder="{{ __('Link (Optional)') }}"
                                                class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                                        </div>
                                        <button type="button" onclick="this.closest('[data-row]').remove()" class="text-red-500 hover:text-red-700 text-sm">×</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-item-btn" class="mt-2 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                {{ __('+ Add Another Item') }}
                            </button>
                        </div>

                        <div class="flex items-center gap-4 pt-4">
                            <x-primary-button type="submit">{{ __('Update Request') }}</x-primary-button>
                            <a href="{{ route('admin.requests') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const unitsOptionsEdit = @json($units->map(fn($u) => ['id' => $u->id, 'name' => $u->symbol ? $u->name . ' (' . $u->symbol . ')' : $u->name])->values());
        document.getElementById('add-item-btn').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const idx = container.querySelectorAll('[data-row]').length;
            let unitOpts = '<option value="">{{ __("None") }}</option>';
            unitsOptionsEdit.forEach(u => { unitOpts += `<option value="${u.id}">${u.name}</option>`; });
            const div = document.createElement('div');
            div.className = 'flex gap-2 items-start flex-wrap';
            div.setAttribute('data-row', 'new');
            div.innerHTML = `
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="items[${idx}][content]" placeholder="{{ __('Item Content') }}"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                </div>
                <div class="min-w-[110px]">
                    <select name="items[${idx}][unit_id]" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">${unitOpts}</select>
                </div>
                <div class="min-w-[80px]">
                    <input type="number" step="any" min="0" name="items[${idx}][quantity]" placeholder="0"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
                </div>
                <div class="flex-1 min-w-[180px]">
                    <input type="text" name="items[${idx}][link]" placeholder="{{ __('Link (Optional)') }}"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                </div>
                <button type="button" onclick="this.closest('[data-row]').remove()" class="text-red-500 hover:text-red-700 text-sm">×</button>
            `;
            container.appendChild(div);
        });
    </script>
</x-app-layout>
