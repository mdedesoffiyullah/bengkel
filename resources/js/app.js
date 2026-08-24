import './bootstrap';

// Work Order spareparts can be purchased directly from the WO form.
// Add a supplier selector without duplicating the large Blade item template.
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
                <option value="">-- Pilih Supplier --</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
                Supplier ini menjadi pihak pembelian dan pembayaran purchase.
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

            // Current database has one active supplier; select it automatically.
            // With multiple suppliers, the user must explicitly choose one.
            if (suppliers.length === 1) {
                select.value = suppliers[0].id;
            }
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
