import './bootstrap';

/* Work Order supplier UI.
 * Supplier controls are rendered directly into PRODUCT rows. The UI is kept
 * independent from the Work Order item-type event so it also works when the
 * row is changed programmatically by the Blade script.
 */
document.addEventListener('DOMContentLoaded', () => {
    if (!window.location.pathname.startsWith('/work-orders')) return;

    const cache = new Map();

    async function getSuppliers(productId = '') {
        const key = productId || 'all';
        if (!cache.has(key)) {
            const url = productId
                ? `/suppliers?json=1&product_id=${encodeURIComponent(productId)}`
                : '/suppliers?json=1';
            cache.set(key, fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            }).then(async response => {
                if (!response.ok) throw new Error(`Supplier HTTP ${response.status}`);
                const data = await response.json();
                return Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
            }));
        }
        return cache.get(key);
    }

    function supplierMarkup(index) {
        return `
            <div class="supplier-box md:col-span-2 mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold mb-1">Supplier Pembelian *</label>
                <div class="relative">
                    <input type="text" autocomplete="off"
                        class="supplier-search w-full rounded-lg border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ketik nama / kode / contact / telepon supplier..."
                        role="combobox" aria-expanded="false">
                    <input type="hidden" name="items[${index}][supplier_id]" class="supplier-id">
                    <input type="hidden" name="items[${index}][supplier_mode]" class="supplier-mode">
                    <div class="supplier-results absolute left-0 right-0 top-full z-[100] mt-1 hidden max-h-64 overflow-auto rounded-lg border border-gray-200 bg-white shadow-xl"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Supplier ini menjadi pihak pembelian dan pembayaran purchase.</p>

                <div class="supplier-new hidden mt-4 rounded-lg border border-dashed border-gray-300 bg-white p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-sm">Tambah Supplier Baru</div>
                            <div class="text-xs text-gray-500">Isi data supplier baru lalu simpan.</div>
                        </div>
                        <button type="button" class="supplier-cancel-new text-xs font-medium text-blue-600 hover:underline">Pilih Supplier Lama</button>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kode Supplier</label>
                            <input name="items[${index}][supplier_code]" class="supplier-new-code w-full rounded-lg border-gray-300" placeholder="Opsional - otomatis jika kosong">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Supplier *</label>
                            <input name="items[${index}][supplier_name]" class="supplier-new-name w-full rounded-lg border-gray-300" placeholder="Nama Supplier">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Contact Person</label>
                            <input name="items[${index}][supplier_contact_person]" class="w-full rounded-lg border-gray-300" placeholder="Nama contact person">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">No. Telepon</label>
                            <input name="items[${index}][supplier_phone]" class="w-full rounded-lg border-gray-300" placeholder="Nomor telepon">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">Alamat</label>
                            <input name="items[${index}][supplier_address]" class="w-full rounded-lg border-gray-300" placeholder="Alamat supplier">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">Catatan</label>
                            <textarea name="items[${index}][supplier_notes]" rows="2" class="w-full rounded-lg border-gray-300" placeholder="Catatan"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async function ensureSupplier(row) {
        if (!row || !row.dataset.index) return;
        const type = row.querySelector('select[name*="[item_type]"]');
        const purchaseBox = row.querySelector('.purchase-quantity-box');
        if (!type || !purchaseBox || type.value !== 'PRODUCT') return;

        let box = row.querySelector('.supplier-box');
        if (!box) {
            purchaseBox.parentElement.insertAdjacentHTML('afterbegin', supplierMarkup(row.dataset.index));
            box = row.querySelector('.supplier-box');

            const search = box.querySelector('.supplier-search');
            const results = box.querySelector('.supplier-results');
            const idInput = box.querySelector('.supplier-id');
            const modeInput = box.querySelector('.supplier-mode');
            const newBox = box.querySelector('.supplier-new');
            const newName = box.querySelector('.supplier-new-name');

            function closeResults() {
                results.classList.add('hidden');
                search.setAttribute('aria-expanded', 'false');
            }

            function choose(supplier) {
                idInput.value = supplier.id || '';
                modeInput.value = 'EXISTING';
                search.value = `${supplier.code || ''} - ${supplier.name || ''}`.replace(/^ - /, '');
                newBox.classList.add('hidden');
                closeResults();
            }

            function openNew() {
                idInput.value = '';
                modeInput.value = 'NEW';
                newBox.classList.remove('hidden');
                if (!newName.value.trim() && search.value.trim()) newName.value = search.value.trim();
                closeResults();
                newName.focus();
            }

            async function render() {
                let suppliers = [];
                try {
                    const productId = row.querySelector('select[name*="[product_id]"]')?.value || '';
                    suppliers = await getSuppliers(productId);
                } catch (error) {
                    console.error(error);
                }

                const q = search.value.trim().toLowerCase();
                results.innerHTML = '';
                suppliers.filter(s => `${s.code || ''} ${s.name || ''} ${s.contact_person || ''} ${s.phone || ''}`.toLowerCase().includes(q)).slice(0, 50).forEach(s => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'block w-full border-b px-3 py-2 text-left text-sm hover:bg-gray-50';
                    button.textContent = `${s.code || ''} - ${s.name || ''}`;
                    button.addEventListener('mousedown', e => e.preventDefault());
                    button.addEventListener('click', () => choose(s));
                    results.appendChild(button);
                });

                const add = document.createElement('button');
                add.type = 'button';
                add.className = 'block w-full px-3 py-3 text-left text-sm font-semibold text-blue-600 hover:bg-blue-50';
                add.textContent = search.value.trim() ? `+ Tambah Supplier Baru: "${search.value.trim()}"` : '+ Tambah Supplier Baru';
                add.addEventListener('mousedown', e => e.preventDefault());
                add.addEventListener('click', openNew);
                results.appendChild(add);
                results.classList.remove('hidden');
                search.setAttribute('aria-expanded', 'true');
            }

            search.addEventListener('focus', render);
            search.addEventListener('input', () => {
                idInput.value = '';
                modeInput.value = '';
                newBox.classList.add('hidden');
                render();
            });
            search.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeResults();
            });
            box.querySelector('.supplier-cancel-new').addEventListener('click', () => {
                modeInput.value = '';
                idInput.value = '';
                newBox.classList.add('hidden');
                search.value = '';
                search.focus();
            });
        }
    }

    function scan() {
        document.querySelectorAll('.item-row[data-index]').forEach(row => ensureSupplier(row));
    }

    scan();
    new MutationObserver(scan).observe(document.body, { childList: true, subtree: true });
    setInterval(scan, 500);
});
