<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Menampilkan semua jasa.
     */
    public function index()
    {
        $services = Service::latest()->paginate(10);

        return view('services.index', compact('services'));
    }

    /**
     * Form tambah jasa.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * Menyimpan jasa baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:services,code',
            'name' => 'required|string|max:255',
            'default_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Service::create($validated);

        return redirect()
            ->route('services.index')
            ->with('success', 'Jasa berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail jasa.
     */
    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    /**
     * Form edit jasa.
     */
    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    /**
     * Memperbarui jasa.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('services', 'code')
                    ->ignore($service->id),
            ],
            'name' => 'required|string|max:255',
            'default_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $service->update($validated);

        return redirect()
            ->route('services.index')
            ->with('success', 'Jasa berhasil diperbarui.');
    }

    /**
     * Menghapus jasa.
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Jasa berhasil dihapus.');
    }
}