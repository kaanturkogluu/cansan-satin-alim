<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" required
                                autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                rows="3"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="text-lg font-medium">{{ __('Items') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Each item can optionally have an image; image name is required when uploading.') }} {{ __('Select unit and enter quantity for each item.') }}</p>
                            <div id="items-container" class="space-y-4 mt-3">
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 space-y-2" data-index="0">
                                    <div class="flex gap-2 items-start flex-wrap">
                                        <div class="flex-1 min-w-[200px]">
                                            <x-input-label for="item_0_content" :value="__('Item Content')" class="text-sm" />
                                            <x-text-input id="item_0_content" name="items[0][content]" class="w-full mt-1" required />
                                        </div>
                                        <div class="min-w-[120px]">
                                            <x-input-label for="item_0_unit" :value="__('Unit')" class="text-sm" />
                                            <select id="item_0_unit" name="items[0][unit_id]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                                <option value="">{{ __('None') }}</option>
                                                @foreach($units as $u)
                                                    <option value="{{ $u->id }}">{{ $u->symbol ? $u->name . ' (' . $u->symbol . ')' : $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="min-w-[100px]">
                                            <x-input-label for="item_0_quantity" :value="__('Quantity')" class="text-sm" />
                                            <x-text-input id="item_0_quantity" name="items[0][quantity]" type="number" step="any" min="0" class="w-full mt-1 text-sm" placeholder="0" />
                                        </div>
                                        <button type="button" onclick="toggleLink(this)" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline self-end">{{ __('Add link') }}</button>
                                        <button type="button" onclick="toggleImage(this)" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline self-end">{{ __('Add image') }}</button>
                                        <button type="button" onclick="removeItem(this)" class="text-red-500 self-end">×</button>
                                    </div>
                                    <div class="item-link-wrap hidden pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <x-input-label for="item_0_link" :value="__('Link (Optional)')" class="text-sm" />
                                        <x-text-input id="item_0_link" name="items[0][link]" class="w-full mt-1" />
                                        <button type="button" onclick="toggleLink(this)" class="mt-1 text-xs text-gray-500 hover:underline">{{ __('Hide link field') }}</button>
                                    </div>
                                    <div class="item-image-wrap hidden pt-2 border-t border-gray-100 dark:border-gray-700 space-y-3">
                                        <div class="flex gap-2 flex-wrap items-center">
                                            <button type="button" onclick="openImageSystemModal(this)" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm font-medium">
                                                {{ __('Select from system') }}
                                            </button>
                                            <button type="button" onclick="toggleImage(this)" class="text-xs text-gray-500 hover:underline">{{ __('Hide image field') }}</button>
                                        </div>
                                        <div class="item-image-system space-y-2">
                                            <input type="hidden" name="items[0][image_from_system_path]" class="item-sys-path" value="" />
                                            <input type="hidden" name="items[0][image_from_system_name]" class="item-sys-name" value="" />
                                            <p class="item-selected-name text-sm text-green-600 dark:text-green-400 hidden">
                                                <span class="item-selected-text"></span>
                                                <button type="button" onclick="clearSystemImageSelection(this)" class="ml-2 text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('Cancel selection') }}</button>
                                            </p>
                                        </div>
                                        <div class="item-image-upload space-y-2">
                                            <p class="item-upload-label text-xs text-gray-500 dark:text-gray-400">{{ __('Or upload image') }}</p>
                                            <div class="flex flex-wrap gap-3 items-end">
                                                <div class="min-w-[160px]">
                                                    <x-input-label for="item_0_image_name" :value="__('Image name')" class="text-sm" />
                                                    <x-text-input id="item_0_image_name" name="items[0][image_name]" type="text" class="w-full mt-1 text-sm" :placeholder="__('Required when uploading image')" />
                                                </div>
                                                <div class="min-w-[180px]">
                                                    <x-input-label for="item_0_image" :value="__('Image file')" class="text-sm" />
                                                    <input type="file" id="item_0_image" name="items[0][image]" accept="image/jpeg,image/png,image/gif,image/webp"
                                                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="addItem()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">{{ __('+ Add Another Item') }}</button>
                        </div>

                        <!-- Modal: Sistemden görsel seç -->
                        <div id="image-system-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" onclick="closeImageSystemModal()"></div>
                                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                                    <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                                        <h3 id="modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Select from system') }}</h3>
                                        <button type="button" onclick="closeImageSystemModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-3 border-b border-gray-200 dark:border-gray-700">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search by image name') }}</label>
                                        <div class="flex gap-2">
                                            <input type="text" id="modal-image-search" class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="{{ __('Image name') }}" onkeydown="if(event.key==='Enter'){event.preventDefault();loadModalImages(1);}" />
                                            <button type="button" onclick="loadModalImages(1)" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm">{{ __('Search') }}</button>
                                        </div>
                                    </div>
                                    <div class="p-4 overflow-y-auto flex-1 min-h-0">
                                        <div id="modal-image-results" class="image-system-modal-grid"></div>
                                        <div id="modal-image-pagination" class="mt-3 flex flex-wrap items-center justify-center gap-2"></div>
                                    </div>
                                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                        <span id="modal-image-info" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                        <button type="button" onclick="closeImageSystemModal()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-600">{{ __('Cancel') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <style>
                            .image-system-modal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.75rem; }
                            .image-system-modal-grid .modal-image-card { width: 100%; cursor: pointer; border-radius: 0.5rem; overflow: hidden; border: 2px solid transparent; transition: border-color 0.2s, box-shadow 0.2s; }
                            .image-system-modal-grid .modal-image-card:hover { border-color: rgb(129 140 248); box-shadow: 0 0 0 1px rgb(129 140 248); }
                            .image-system-modal-grid .modal-image-card.selected { border-color: rgb(99 102 241); box-shadow: 0 0 0 2px rgb(99 102 241); }
                            .image-system-modal-grid .modal-image-card .modal-image-thumb { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; background: #f3f4f6; }
                            .image-system-modal-grid .modal-image-card .modal-image-name { font-size: 0.7rem; padding: 0.25rem; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; color: inherit; }
                        </style>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Submit Request') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 1;
        const unitsOptions = @json($units->map(fn($u) => ['id' => $u->id, 'name' => $u->symbol ? $u->name . ' (' . $u->symbol . ')' : $u->name])->values());
        function unitSelectHtml(idx) {
            let opts = '<option value="">{{ __("None") }}</option>';
            unitsOptions.forEach(u => { opts += `<option value="${u.id}">${u.name}</option>`; });
            return `<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Unit') }}</label>
                <select name="items[${idx}][unit_id]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm">${opts}</select>`;
        }
        function toggleLink(btn) {
            const wrap = btn.closest('[data-index]').querySelector('.item-link-wrap');
            if (wrap) wrap.classList.toggle('hidden');
        }
        function toggleImage(btn) {
            const wrap = btn.closest('[data-index]').querySelector('.item-image-wrap');
            if (wrap) wrap.classList.toggle('hidden');
        }
        let currentImageTargetWrap = null;
        const searchImagesUrl = '{{ route("requests.search-images") }}';
        const perPage = 12;
        function openImageSystemModal(btn) {
            currentImageTargetWrap = btn.closest('.item-image-wrap');
            const modal = document.getElementById('image-system-modal');
            const searchInput = document.getElementById('modal-image-search');
            if (modal) modal.classList.remove('hidden');
            if (searchInput) { searchInput.value = ''; searchInput.focus(); }
            loadModalImages(1);
        }
        function closeImageSystemModal() {
            document.getElementById('image-system-modal').classList.add('hidden');
            currentImageTargetWrap = null;
        }
        function clearSystemImageSelection(btn) {
            const wrap = btn.closest('.item-image-wrap');
            if (!wrap) return;
            const pathInput = wrap.querySelector('.item-sys-path');
            const nameInput = wrap.querySelector('.item-sys-name');
            const selectedP = wrap.querySelector('.item-selected-name');
            const uploadSection = wrap.querySelector('.item-image-upload');
            const fileInput = wrap.querySelector('input[type="file"][name*="[image]"]');
            const nameField = wrap.querySelector('input[name*="[image_name]"]');
            if (pathInput) pathInput.value = '';
            if (nameInput) nameInput.value = '';
            if (selectedP) {
                const textSpan = selectedP.querySelector('.item-selected-text');
                if (textSpan) textSpan.textContent = '';
                selectedP.classList.add('hidden');
            }
            if (uploadSection) uploadSection.classList.remove('hidden');
            if (fileInput) { fileInput.value = ''; fileInput.disabled = false; }
            if (nameField) { nameField.value = ''; nameField.disabled = false; }
        }
        function applySystemSelection(wrap, img) {
            const pathInput = wrap.querySelector('.item-sys-path');
            const nameInput = wrap.querySelector('.item-sys-name');
            const selectedP = wrap.querySelector('.item-selected-name');
            const selectedText = selectedP && selectedP.querySelector('.item-selected-text');
            const uploadSection = wrap.querySelector('.item-image-upload');
            const fileInput = wrap.querySelector('input[type="file"][name*="[image]"]');
            const nameField = wrap.querySelector('input[name*="[image_name]"]');
            if (pathInput) pathInput.value = img.path || '';
            if (nameInput) nameInput.value = img.name || '';
            if (selectedText) selectedText.textContent = '{{ __("Selected") }}: ' + (img.name || '');
            if (selectedP) selectedP.classList.remove('hidden');
            if (uploadSection) uploadSection.classList.add('hidden');
            if (fileInput) { fileInput.value = ''; fileInput.disabled = true; }
            if (nameField) { nameField.value = ''; nameField.disabled = true; }
        }
        function buildSearchParams(page, q) {
            const params = new URLSearchParams({ page: String(page), per_page: String(perPage) });
            if (q) params.set('q', q);
            return params.toString();
        }
        async function loadModalImages(page) {
            const searchInput = document.getElementById('modal-image-search');
            const resultsEl = document.getElementById('modal-image-results');
            const paginationEl = document.getElementById('modal-image-pagination');
            const infoEl = document.getElementById('modal-image-info');
            const q = (searchInput && searchInput.value || '').trim();
            resultsEl.innerHTML = '<span class="text-sm text-gray-500 dark:text-gray-400 col-span-full">{{ __("Loading...") }}</span>';
            paginationEl.innerHTML = '';
            infoEl.textContent = '';
            try {
                const url = searchImagesUrl + '?' + buildSearchParams(page, q);
                const r = await fetch(url);
                const data = await r.json();
                const images = data.images || [];
                const currentPage = data.current_page || 1;
                const lastPage = data.last_page || 1;
                const total = data.total || 0;
                resultsEl.innerHTML = '';
                if (images.length === 0) {
                    resultsEl.innerHTML = '<span class="text-sm text-gray-500 dark:text-gray-400 col-span-full">' + (q ? '{{ __("No images found.") }}' : '{{ __("No images in system.") }}') + '</span>';
                } else {
                    images.forEach(img => {
                        const card = document.createElement('div');
                        card.className = 'modal-image-card';
                        card.innerHTML = `<img src="${img.url}" alt="" class="modal-image-thumb" /><span class="modal-image-name" title="${(img.name || '').replace(/"/g, '&quot;')}">${img.name || ''}</span>`;
                        card.onclick = () => {
                            if (currentImageTargetWrap) applySystemSelection(currentImageTargetWrap, img);
                            resultsEl.querySelectorAll('.modal-image-card').forEach(el => el.classList.remove('selected'));
                            card.classList.add('selected');
                            setTimeout(closeImageSystemModal, 300);
                        };
                        resultsEl.appendChild(card);
                    });
                    const from = (currentPage - 1) * perPage + 1;
                    const to = Math.min(currentPage * perPage, total);
                    infoEl.textContent = total ? (from + '-' + to + ' / ' + total) : '';
                    if (lastPage > 1) {
                        const prevBtn = document.createElement('button');
                        prevBtn.type = 'button';
                        prevBtn.className = 'px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50';
                        prevBtn.textContent = '{{ __("Previous") }}';
                        prevBtn.disabled = currentPage <= 1;
                        prevBtn.onclick = () => loadModalImages(currentPage - 1);
                        paginationEl.appendChild(prevBtn);
                        const pageInfo = document.createElement('span');
                        pageInfo.className = 'text-sm text-gray-600 dark:text-gray-400';
                        pageInfo.textContent = currentPage + ' / ' + lastPage;
                        paginationEl.appendChild(pageInfo);
                        const nextBtn = document.createElement('button');
                        nextBtn.type = 'button';
                        nextBtn.className = 'px-2 py-1 text-sm rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50';
                        nextBtn.textContent = '{{ __("Next") }}';
                        nextBtn.disabled = currentPage >= lastPage;
                        nextBtn.onclick = () => loadModalImages(currentPage + 1);
                        paginationEl.appendChild(nextBtn);
                    }
                }
            } catch (e) {
                resultsEl.innerHTML = '<span class="text-sm text-red-500 col-span-full">{{ __("Search failed.") }}</span>';
            }
        }
        function removeItem(btn) {
            const container = document.getElementById('items-container');
            if (container.querySelectorAll('[data-index]').length <= 1) return;
            btn.closest('[data-index]').remove();
        }
        function addItem() {
            const container = document.getElementById('items-container');
            const row = document.createElement('div');
            row.className = 'border border-gray-200 dark:border-gray-600 rounded-lg p-3 space-y-2';
            row.setAttribute('data-index', itemIndex);
            row.innerHTML = `
                <div class="flex gap-2 items-start flex-wrap">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Item Content') }}</label>
                        <input type="text" name="items[${itemIndex}][content]" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div class="min-w-[120px]">${unitSelectHtml(itemIndex)}</div>
                    <div class="min-w-[100px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Quantity') }}</label>
                        <input type="number" step="any" min="0" name="items[${itemIndex}][quantity]" placeholder="0" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
                    </div>
                    <button type="button" onclick="toggleLink(this)" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline self-end">{{ __('Add link') }}</button>
                    <button type="button" onclick="toggleImage(this)" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline self-end">{{ __('Add image') }}</button>
                    <button type="button" onclick="removeItem(this)" class="text-red-500 self-end">×</button>
                </div>
                <div class="item-link-wrap hidden pt-2 border-t border-gray-100 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Link (Optional)') }}</label>
                    <input type="text" name="items[${itemIndex}][link]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" />
                    <button type="button" onclick="toggleLink(this)" class="mt-1 text-xs text-gray-500 hover:underline">{{ __('Hide link field') }}</button>
                </div>
                <div class="item-image-wrap hidden pt-2 border-t border-gray-100 dark:border-gray-700 space-y-3">
                    <div class="flex gap-2 flex-wrap items-center">
                        <button type="button" onclick="openImageSystemModal(this)" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm font-medium">{{ __('Select from system') }}</button>
                        <button type="button" onclick="toggleImage(this)" class="text-xs text-gray-500 hover:underline">{{ __('Hide image field') }}</button>
                    </div>
                    <div class="item-image-system space-y-2">
                        <input type="hidden" name="items[${itemIndex}][image_from_system_path]" class="item-sys-path" value="" />
                        <input type="hidden" name="items[${itemIndex}][image_from_system_name]" class="item-sys-name" value="" />
                        <p class="item-selected-name text-sm text-green-600 dark:text-green-400 hidden">
                            <span class="item-selected-text"></span>
                            <button type="button" onclick="clearSystemImageSelection(this)" class="ml-2 text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('Cancel selection') }}</button>
                        </p>
                    </div>
                    <div class="item-image-upload space-y-2">
                        <p class="item-upload-label text-xs text-gray-500 dark:text-gray-400">{{ __('Or upload image') }}</p>
                        <div class="flex flex-wrap gap-3 items-end">
                            <div class="min-w-[160px]">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Image name') }}</label>
                                <input type="text" name="items[${itemIndex}][image_name]" placeholder="{{ __('Required when uploading image') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
                            </div>
                            <div class="min-w-[180px]">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Image file') }}</label>
                                <input type="file" name="items[${itemIndex}][image]" accept="image/jpeg,image/png,image/gif,image/webp" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300" />
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(row);
            itemIndex++;
        }
    </script>
</x-app-layout>