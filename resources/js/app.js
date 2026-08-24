import './bootstrap';

const initSearchableSelects = (root = document) => {
    root.querySelectorAll('select:not([multiple]):not([data-searchable-ready="1"])').forEach(select => {
        if (select.closest('[data-searchable-wrapper="1"]')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'relative searchable-select-wrapper';
        wrapper.dataset.searchableWrapper = '1';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        const options = Array.from(select.options);
        const placeholderOption = options.find(option => option.value === '');
        const placeholder = placeholderOption?.textContent?.trim() || 'Pilih...';

        const input = document.createElement('input');
        input.type = 'text';
        input.autocomplete = 'off';
        input.className = 'w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
        input.placeholder = placeholder;
        input.disabled = select.disabled;
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-autocomplete', 'list');

        const dropdown = document.createElement('div');
        dropdown.className = 'absolute z-50 mt-1 hidden w-full max-h-60 overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg';
        dropdown.setAttribute('role', 'listbox');

        select.classList.add('hidden');
        select.dataset.searchableReady = '1';
        wrapper.appendChild(input);
        wrapper.appendChild(dropdown);

        const syncInput = () => {
            const selected = select.options[select.selectedIndex];
            input.value = selected && selected.value !== '' ? selected.textContent.trim() : '';
            input.placeholder = selected && selected.value !== '' ? '' : placeholder;
        };

        const closeDropdown = () => {
            dropdown.classList.add('hidden');
            input.setAttribute('aria-expanded', 'false');
        };

        const renderOptions = (query = '') => {
            const normalized = query.trim().toLowerCase();
            dropdown.innerHTML = '';

            options.forEach(option => {
                if (option.disabled) return;
                const text = option.textContent.trim();
                if (option.value !== '' && normalized && !text.toLowerCase().includes(normalized)) return;

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-gray-50';
                button.textContent = text || placeholder;

                if (option.value === select.value) {
                    button.classList.add('bg-gray-50', 'font-medium');
                }

                button.addEventListener('mousedown', event => event.preventDefault());
                button.addEventListener('click', () => {
                    select.value = option.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncInput();
                    closeDropdown();
                });

                dropdown.appendChild(button);
            });

            if (!dropdown.children.length) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-sm text-gray-500';
                empty.textContent = 'Tidak ada hasil.';
                dropdown.appendChild(empty);
            }
        };

        const openDropdown = () => {
            if (select.disabled) return;
            dropdown.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
            renderOptions(input.value);
        };

        input.addEventListener('focus', openDropdown);
        input.addEventListener('click', openDropdown);
        input.addEventListener('input', () => {
            openDropdown();
            renderOptions(input.value);
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                closeDropdown();
                syncInput();
            } else if (event.key === 'Enter') {
                const first = dropdown.querySelector('button');
                if (first) {
                    event.preventDefault();
                    first.click();
                }
            }
        });

        select.addEventListener('change', syncInput);
        syncInput();
    });
};

document.addEventListener('click', event => {
    document.querySelectorAll('[data-searchable-wrapper="1"]').forEach(wrapper => {
        if (!wrapper.contains(event.target)) {
            wrapper.querySelector('[role="listbox"]')?.classList.add('hidden');
            wrapper.querySelector('[role="combobox"]')?.setAttribute('aria-expanded', 'false');
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    initSearchableSelects();

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) initSearchableSelects(node);
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});

if (window.location.pathname.startsWith('/work-orders')) {
    const supplierCache = new Map();

    const loadSuppliers = async productId => {
        const key = productId || 'all';

        if (!supplierCache.has(key)) {
            const url = productId
                ? `/suppliers?json=1&product_id=${encodeURIComponent(productId)}`
                : '/suppliers?json=1';

            supplierCache.set(
                key,
                fetch(url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin'
                }).then(response => {
                    if (!response.ok) throw new Error('Gagal memuat supplier.');
                    return response.json();
                })
            );
        }

        return supplierCache.get(key);
    };

    const addSupplierSelector = async row => {
        if (!row) return;

        const typeSelect = row.querySelector('select[name*="[item_type]"]');
        const index = row.dataset.index;
        const purchaseBox = row.querySelector('.purchase-quantity-box');

        if (!typeSelect || index === undefined || !purchaseBox) return;

        let wrapper = row.querySelector('.supplier-box');

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'supplier-box md:col-span-2';
            wrapper.innerHTML = `
                <label class="block text-sm font-medium mb-1">Supplier Pembelian</label>
                <div class="relative">
                    <input type="text" autocomplete="off"
                           class="supplier-search w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Ketik nama/kode supplier..."
                           role="combobox" aria-expanded="false" aria-autocomplete="list">
                    <input type="hidden" name="items[${index}][supplier_id]" class="supplier-id">
                    <input type="hidden" name="items[${index}][supplier_mode]" class="supplier-mode" value="">
                    <div class="supplier-results absolute z-50 mt-1 hidden w-full max-h-64 overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg"></div>
                </div>
                <div class="supplier-new hidden mt-3 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-semibold text-sm">Supplier Baru</p>
                            <p class="text-xs text-gray-500">Data ini akan disimpan ke Master Supplier.</p>
                        </div>
                        <button type="button" class="supplier-cancel-new text-xs text-blue-600">Pilih Supplier Lama</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input name="items[${index}][supplier_code]" class="supplier-new-code rounded-lg border-gray-300" placeholder="Kode Supplier (opsional)">
                        <input name="items[${index}][supplier_name]" class="supplier-new-name rounded-lg border-gray-300" placeholder="Nama Supplier *">
                        <input name="items[${index}][supplier_contact_person]" class="rounded-lg border-gray-300" placeholder="Contact Person">
                        <input name="items[${index}][supplier_phone]" class="rounded-lg border-gray-300" placeholder="No. Telepon">
                        <input name="items[${index}][supplier_address]" class="rounded-lg border-gray-300 md:col-span-2" placeholder="Alamat">
                        <textarea name="items[${index}][supplier_notes]" rows="2" class="rounded-lg border-gray-300 md:col-span-2" placeholder="Catatan"></textarea>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1 supplier-help">Cari supplier lama berdasarkan kode/nama. Supplier yang pernah dipakai untuk produk ini ditampilkan lebih dulu.</p>
            `;

            purchaseBox.parentElement?.insertBefore(wrapper, purchaseBox);

            const search = wrapper.querySelector('.supplier-search');
            const results = wrapper.querySelector('.supplier-results');
            const hiddenId = wrapper.querySelector('.supplier-id');
            const supplierMode = wrapper.querySelector('.supplier-mode');
            const newBox = wrapper.querySelector('.supplier-new');
            const newName = wrapper.querySelector('.supplier-new-name');
            let suppliers = [];

            const closeResults = () => {
                results.classList.add('hidden');
                search.setAttribute('aria-expanded', 'false');
            };

            const selectExistingSupplier = supplier => {
                hiddenId.value = supplier.id;
                supplierMode.value = 'EXISTING';
                search.value = `${supplier.code} - ${supplier.name}`;
                newBox.classList.add('hidden');
                closeResults();
            };

            const openNewSupplier = query => {
                hiddenId.value = '';
                supplierMode.value = 'NEW';
                newBox.classList.remove('hidden');

                if (query.trim() && !newName.value) {
                    newName.value = query.trim();
                }

                closeResults();
                newName.focus();
            };

            const renderResults = query => {
                const normalized = query.trim().toLowerCase();
                results.innerHTML = '';

                suppliers
                    .filter(supplier => `${supplier.code} ${supplier.name} ${supplier.contact_person || ''} ${supplier.phone || ''}`.toLowerCase().includes(normalized))
                    .slice(0, 50)
                    .forEach(supplier => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'block w-full px-3 py-2 text-left text-sm hover:bg-gray-50';

                        const title = document.createElement('span');
                        title.className = 'font-medium';
                        title.textContent = `${supplier.code} - ${supplier.name}`;
                        button.appendChild(title);

                        if (supplier.phone) {
                            const phone = document.createElement('span');
                            phone.className = 'block text-xs text-gray-500';
                            phone.textContent = supplier.phone;
                            button.appendChild(phone);
                        }

                        button.addEventListener('mousedown', event => event.preventDefault());
                        button.addEventListener('click', () => selectExistingSupplier(supplier));
                        results.appendChild(button);
                    });

                const newButton = document.createElement('button');
                newButton.type = 'button';
                newButton.className = 'block w-full border-t px-3 py-2 text-left text-sm font-medium text-blue-600 hover:bg-blue-50';
                newButton.textContent = `+ Tambah Supplier Baru${query.trim() ? `: "${query.trim()}"` : ''}`;
                newButton.addEventListener('mousedown', event => event.preventDefault());
                newButton.addEventListener('click', () => openNewSupplier(query));
                results.appendChild(newButton);

                results.classList.remove('hidden');
                search.setAttribute('aria-expanded', 'true');
            };

            const refreshSuppliers = async () => {
                try {
                    const productId = row.querySelector('select[name*="[product_id]"]')?.value || '';
                    suppliers = await loadSuppliers(productId);
                } catch (error) {
                    console.error(error);
                    suppliers = [];
                }
            };

            search.addEventListener('focus', () => renderResults(search.value));
            search.addEventListener('input', () => {
                hiddenId.value = '';
                supplierMode.value = '';
                newBox.classList.add('hidden');
                renderResults(search.value);
            });
            search.addEventListener('keydown', event => {
                if (event.key === 'Escape') closeResults();
                if (event.key === 'Enter') {
                    const first = results.querySelector('button');
                    if (first) {
                        event.preventDefault();
                        first.click();
                    }
                }
            });

            wrapper.querySelector('.supplier-cancel-new').addEventListener('click', () => {
                supplierMode.value = '';
                hiddenId.value = '';
                newBox.classList.add('hidden');
                search.focus();
            });

            row.querySelectorAll('select[name*="[product_id]"]').forEach(select => {
                select.addEventListener('change', async () => {
                    hiddenId.value = '';
                    supplierMode.value = '';
                    search.value = '';
                    newBox.classList.add('hidden');
                    await refreshSuppliers();
                });
            });

            await refreshSuppliers();
        }

        // Supplier hanya relevan untuk Sparepart.
        if (typeSelect.value === 'PRODUCT') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    };

    const scanRows = () => {
        document.querySelectorAll('.item-row').forEach(row => addSupplierSelector(row));
    };

    const observer = new MutationObserver(scanRows);
    observer.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('change', event => {
        const typeSelect = event.target.closest?.('select[name*="[item_type]"]');
        if (!typeSelect) return;

        const row = typeSelect.closest('.item-row');
        if (row) addSupplierSelector(row);
    });

    scanRows();
}
