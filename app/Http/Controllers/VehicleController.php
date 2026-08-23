<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Menampilkan semua kendaraan.
     */
    public function index()
    {
        $vehicles = Vehicle::with('customer')
            ->latest()
            ->paginate(10);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Form tambah kendaraan.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('vehicles.create', compact('customers'));
    }

    /**
     * Menyimpan kendaraan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:vehicles,code',

            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')
                    ->where('is_active', true),
            ],

            'plate_number' => 'required|string|max:20',

            'brand' => 'required|string|max:100',

            'type' => 'required|string|max:100',

            'notes' => 'nullable|string',

            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Vehicle::create($validated);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan.');
    }

    /**
     * Detail kendaraan.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load('customer');

        return view('vehicles.show', compact('vehicle'));
    }

    /**
     * Form edit kendaraan.
     */
    public function edit(Vehicle $vehicle)
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'vehicles.edit',
            compact('vehicle', 'customers')
        );
    }

    /**
     * Memperbarui kendaraan.
     */
    public function update(
        Request $request,
        Vehicle $vehicle
    ) {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'code')
                    ->ignore($vehicle->id),
            ],

            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')
                    ->where('is_active', true),
            ],

            'plate_number' => 'required|string|max:20',

            'brand' => 'required|string|max:100',

            'type' => 'required|string|max:100',

            'notes' => 'nullable|string',

            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $vehicle->update($validated);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil diperbarui.');
    }

    /**
     * Menghapus kendaraan.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }
}
