<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    /**
     * Menampilkan semua kategori produk.
     */
    public function index()
    {
        $categories = ProductCategory::latest()->paginate(10);

        return view('product_categories.index', compact('categories'));
    }

    /**
     * Form tambah kategori.
     */
    public function create()
    {
        return view('product_categories.create');
    }

    /**
     * Menyimpan kategori baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:product_categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        ProductCategory::create($validated);

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Kategori produk berhasil ditambahkan.');
    }

    /**
     * Detail kategori.
     */
    public function show(ProductCategory $productCategory)
    {
        $productCategory->load('products');

        return view(
            'product_categories.show',
            compact('productCategory')
        );
    }

    /**
     * Form edit kategori.
     */
    public function edit(ProductCategory $productCategory)
    {
        return view(
            'product_categories.edit',
            compact('productCategory')
        );
    }

    /**
     * Memperbarui kategori.
     */
    public function update(
        Request $request,
        ProductCategory $productCategory
    ) {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('product_categories', 'code')
                    ->ignore($productCategory->id),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $productCategory->update($validated);

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Kategori produk berhasil diperbarui.');
    }

    /**
     * Menghapus kategori.
     */
    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Kategori produk berhasil dihapus.');
    }
}