<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\InventoryBalance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->latest()
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:products,code',

            'category_name' => 'nullable|string|max:255',

            'barcode' => 'nullable|string|max:100|unique:products,barcode',

            'name' => 'required|string|max:255',

            'brand' => 'nullable|string|max:100',

            'unit' => 'required|string|max:20',

            'stock_type' => [
                'required',
                Rule::in(['STOCK', 'NON_STOCK']),
            ],

            'last_buy_price' =>
                'required|numeric|min:0',

            'selling_price' =>
                'required|numeric|min:0',

            'minimum_stock' =>
                'required|numeric|min:0',

            'is_active' => 'nullable|boolean',

            'notes' => 'nullable|string',
        ]);

        $category = $this->getOrCreateCategory(
            $validated['category_name'] ?? null
        );

        $product = Product::create([
            'code' => $validated['code'],
            'category_id' => $category->id,
            'barcode' => $validated['barcode'] ?? null,
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'unit' => $validated['unit'],
            'stock_type' => $validated['stock_type'],
            'last_buy_price' =>
                $validated['last_buy_price'],
            'selling_price' =>
                $validated['selling_price'],
            'minimum_stock' =>
                $validated['minimum_stock'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $validated['notes'] ?? null,
        ]);

        InventoryBalance::firstOrCreate(
            [
                'product_id' => $product->id,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'average_cost' =>
                    $validated['last_buy_price'],
            ]
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil ditambahkan.'
            );
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view(
            'products.show',
            compact('product')
        );
    }

    public function edit(Product $product)
    {
        return view(
            'products.edit',
            compact('product')
        );
    }

    public function update(
        Request $request,
        Product $product
    ) {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique(
                    'products',
                    'code'
                )->ignore($product->id),
            ],

            'category_name' =>
                'nullable|string|max:255',

            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique(
                    'products',
                    'barcode'
                )->ignore($product->id),
            ],

            'name' => 'required|string|max:255',

            'brand' => 'nullable|string|max:100',

            'unit' => 'required|string|max:20',

            'stock_type' => [
                'required',
                Rule::in([
                    'STOCK',
                    'NON_STOCK',
                ]),
            ],

            'last_buy_price' =>
                'required|numeric|min:0',

            'selling_price' =>
                'required|numeric|min:0',

            'minimum_stock' =>
                'required|numeric|min:0',

            'is_active' => 'nullable|boolean',

            'notes' => 'nullable|string',
        ]);

        $category = $this->getOrCreateCategory(
            $validated['category_name'] ?? null
        );

        $product->update([
            'code' => $validated['code'],
            'category_id' => $category->id,
            'barcode' => $validated['barcode'] ?? null,
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'unit' => $validated['unit'],
            'stock_type' => $validated['stock_type'],
            'last_buy_price' =>
                $validated['last_buy_price'],
            'selling_price' =>
                $validated['selling_price'],
            'minimum_stock' =>
                $validated['minimum_stock'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil diperbarui.'
            );
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil dihapus.'
            );
    }

    private function getOrCreateCategory(
        ?string $categoryName
    ): ProductCategory {

        $categoryName = trim(
            (string) $categoryName
        );

        if ($categoryName === '') {
            return ProductCategory::firstOrCreate(
                [
                    'code' => 'SPAREPART-MANUAL',
                ],
                [
                    'name' => 'Sparepart Manual',
                    'description' =>
                        'Kategori default untuk produk tanpa kategori khusus.',
                    'is_active' => true,
                ]
            );
        }

        $existing = ProductCategory::where(
            'name',
            $categoryName
        )->first();

        if ($existing) {
            return $existing;
        }

        $base = strtoupper(
            \Illuminate\Support\Str::slug(
                $categoryName,
                '-'
            )
        );

        $base = substr(
            $base ?: 'CATEGORY',
            0,
            15
        );

        $code = $base;
        $counter = 1;

        while (
            ProductCategory::where(
                'code',
                $code
            )->exists()
        ) {
            $code = $base . '-' . $counter;
            $counter++;
        }

        return ProductCategory::create([
            'code' => $code,
            'name' => $categoryName,
            'is_active' => true,
        ]);
    }
}

