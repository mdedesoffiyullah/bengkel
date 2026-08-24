<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('json')) {
            return response()->json(Supplier::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']));
        }
        $suppliers = Supplier::latest()->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create() { return view('suppliers.create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:suppliers,code', 'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30', 'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string', 'notes' => 'nullable|string', 'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier) { return view('suppliers.show', compact('supplier')); }
    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('suppliers', 'code')->ignore($supplier->id)],
            'name' => 'required|string|max:255', 'phone' => 'nullable|string|max:30',
            'contact_person' => 'nullable|string|max:255', 'address' => 'nullable|string',
            'notes' => 'nullable|string', 'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
