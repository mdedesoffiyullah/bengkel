import './bootstrap';

/*
 * Global searchable select.
 *
 * Every normal single-select on the application becomes a searchable
 * combobox while the original <select> remains in the DOM and continues
 * to be the field submitted by the form. This keeps existing validation,
 * old values and controller contracts intact.
 */
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

        // Hide the native control visually but keep it active for form submission.
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

        const openDropdown = () => {
            if (select.disabled) return;
            dropdown.classList.remove('hidden');
            input.setAttribute('aria-expanded', 'true');
            renderOptions(input.value);
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

const closeSearchableSelects = event => {
    document.querySelectorAll('[data-searchable-wrapper="1"]').forEach(wrapper => {
        if (!wrapper.contains(event.target)) {
            wrapper.querySelector('[role="listbox"]')?.classList.add('hidden');
            wrapper.querySelector('[role="combobox"]')?.setAttribute('aria-expanded', 'false');
        }
    });
};

document.addEventListener('click', closeSearchableSelects);

document.addEventListener('DOMContentLoaded', () => {
    initSearchableSelects();

    // Work Order rows are added dynamically, so watch for new selects.
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    initSearchableSelects(node);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
});

// Work Order spareparts can be purchased directly from the WO form.
// Add a searchable supplier selector without duplicating the large Blade item template.
if (window.location.pathname.startsWith('/work-orders')) {
    let suppliersPromise = null;

    const loadSuppliers = () => {
        if (!suppliersPromise) {
            suppliersPromise = fetch('/suppliers?json=1', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            }).then(response => {
                if (!response.ok) throw new Error('Gagal memuat supplier.');
                return response.json();
            });
        }
        return suppliersPromise;
    };

    const addSupplierSelector = async (row) => {
        if (!row || row.dataset.supplierReady === '1') return;

        const type = row.querySelector('select[name*="[item_type]"]')?.value;
        if (type !== 'PRODUCT') return;

        const index = row.dataset.index;
        const purchaseBox = row.querySelector('.purchase-quantity-box');
        if (!purchaseBox) return;

        row.dataset.supplierReady = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'supplier-box md:col-span-2';
        wrapper.innerHTML = `
            <label class="block text-sm font-medium mb-1">Supplier Pembelian</label>
            <select name="items[${index}][supplier_id]"
                    class="w-full rounded-lg border-gray-300 supplier-select">
                <option value="">-- Cari Supplier --</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
                Ketik kode atau nama supplier untuk mencari. Supplier ini menjadi pihak pembelian dan pembayaran purchase.
            </p>
        `;

        purchaseBox.parentElement?.insertBefore(wrapper, purchaseBox);

        try {
            const suppliers = await loadSuppliers();
            const select = wrapper.querySelector('select');
            suppliers.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.id;
                option.textContent = `${supplier.code} - ${supplier.name}`;
                select.appendChild(option);
            });

            // Do not auto-select a supplier. The operator must choose explicitly.
            select.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (error) {
            console.error(error);
            row.dataset.supplierReady = '0';
        }
    };

    const scanRows = () => {
        document.querySelectorAll('.item-row').forEach(row => {
            addSupplierSelector(row);
        });
    };

    const observer = new MutationObserver(scanRows);
    observer.observe(document.body, { childList: true, subtree: true });
    scanRows();
}
