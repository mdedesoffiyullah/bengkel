<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    /**
     * Menampilkan daftar complaint.
     */
    public function index(Request $request)
    {
        $query = Complaint::with('customer');

        if ($request->filled('customer_id')) {
            $query->where(
                'customer_id',
                $request->customer_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->priority
            );
        }

        $complaints = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'complaints.index',
            compact('complaints')
        );
    }

    /**
     * Form membuat complaint.
     */
    public function create()
    {
        $customers = Customer::where(
            'is_active',
            true
        )
            ->orderBy('name')
            ->get();

        return view(
            'complaints.create',
            compact('customers')
        );
    }

    /**
     * Menyimpan complaint baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists(
                    'customers',
                    'id'
                ),
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'priority' => [
                'required',
                Rule::in([
                    'LOW',
                    'MEDIUM',
                    'HIGH',
                    'URGENT',
                ]),
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'OPEN',
                    'IN_PROGRESS',
                    'RESOLVED',
                    'CLOSED',
                ]),
            ],

            'resolution' =>
                'nullable|string',

            'notes' =>
                'nullable|string',
        ]);

        $complaint = Complaint::create([
            'customer_id' =>
                $validated['customer_id'],

            'subject' =>
                $validated['subject'],

            'description' =>
                $validated['description'],

            'priority' =>
                $validated['priority'],

            'status' =>
                $validated['status']
                ?? 'OPEN',

            'resolution' =>
                $validated['resolution']
                ?? null,

            'notes' =>
                $validated['notes']
                ?? null,
        ]);

        return redirect()
            ->route(
                'complaints.show',
                $complaint
            )
            ->with(
                'success',
                'Complaint berhasil dibuat.'
            );
    }

    /**
     * Detail complaint.
     */
    public function show(
        Complaint $complaint
    ) {
        $complaint->load(
            'customer'
        );

        return view(
            'complaints.show',
            compact('complaint')
        );
    }

    /**
     * Form edit complaint.
     */
    public function edit(
        Complaint $complaint
    ) {
        $customers = Customer::where(
            'is_active',
            true
        )
            ->orderBy('name')
            ->get();

        return view(
            'complaints.edit',
            compact(
                'complaint',
                'customers'
            )
        );
    }

    /**
     * Update complaint.
     */
    public function update(
        Request $request,
        Complaint $complaint
    ) {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists(
                    'customers',
                    'id'
                ),
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'priority' => [
                'required',
                Rule::in([
                    'LOW',
                    'MEDIUM',
                    'HIGH',
                    'URGENT',
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    'OPEN',
                    'IN_PROGRESS',
                    'RESOLVED',
                    'CLOSED',
                ]),
            ],

            'resolution' =>
                'nullable|string',

            'notes' =>
                'nullable|string',
        ]);

        /*
         * Jika complaint sudah RESOLVED/CLOSED,
         * resolution sebaiknya tetap dicatat.
         */
        if (
            in_array(
                $validated['status'],
                [
                    'RESOLVED',
                    'CLOSED',
                ]
            )
            && empty(
                $validated['resolution']
            )
        ) {
            return back()
                ->withErrors([
                    'resolution' =>
                        'Resolution wajib diisi ketika complaint sudah diselesaikan atau ditutup.',
                ])
                ->withInput();
        }

        $complaint->update([
            'customer_id' =>
                $validated['customer_id'],

            'subject' =>
                $validated['subject'],

            'description' =>
                $validated['description'],

            'priority' =>
                $validated['priority'],

            'status' =>
                $validated['status'],

            'resolution' =>
                $validated['resolution']
                ?? null,

            'notes' =>
                $validated['notes']
                ?? null,
        ]);

        return redirect()
            ->route(
                'complaints.show',
                $complaint
            )
            ->with(
                'success',
                'Complaint berhasil diperbarui.'
            );
    }

    /**
     * Complaint tidak dihapus secara permanen.
     */
    public function destroy(
        Complaint $complaint
    ) {
        /*
         * Untuk menjaga histori pelayanan customer,
         * complaint ditutup daripada dihapus.
         */
        $complaint->update([
            'status' => 'CLOSED',
        ]);

        return redirect()
            ->route(
                'complaints.index'
            )
            ->with(
                'success',
                'Complaint berhasil ditutup.'
            );
    }
}