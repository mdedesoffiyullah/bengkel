@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Welcome --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Dashboard
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Selamat datang di Bengkel Management System.
        </p>
    </div>


    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Total Customer
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                0
            </p>
        </div>


        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Kendaraan
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                0
            </p>
        </div>


        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Work Order Aktif
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                0
            </p>
        </div>


        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500">
                Stok Menipis
            </p>

            <p class="mt-2 text-3xl font-bold text-gray-900">
                0
            </p>
        </div>

    </div>


    {{-- Main Dashboard --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Recent Work Orders --}}
        <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200">

            <div class="p-6 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">
                    Work Order Terbaru
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Daftar pekerjaan bengkel terbaru.
                </p>
            </div>

            <div class="p-6">

                <div class="text-center py-10">

                    <p class="text-gray-500">
                        Belum ada work order.
                    </p>

                    <a href="#"
                       class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800">
                        Buat Work Order
                    </a>

                </div>

            </div>

        </div>


        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-200">

            <div class="p-6 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">
                    Quick Actions
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Akses cepat ke aktivitas utama.
                </p>
            </div>

            <div class="p-6 space-y-3">

                <a href="#"
                   class="block w-full px-4 py-3 rounded-lg bg-slate-900 text-white text-sm text-center hover:bg-slate-800">
                    + Buat Work Order
                </a>

                <a href="{{ route('customers.create') }}"
                   class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-700 text-sm text-center hover:bg-gray-50">
                    + Tambah Customer
                </a>

                <a href="#"
                   class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-700 text-sm text-center hover:bg-gray-50">
                    + Tambah Produk
                </a>

                <a href="#"
                   class="block w-full px-4 py-3 rounded-lg border border-gray-300 text-gray-700 text-sm text-center hover:bg-gray-50">
                    + Buat Pembelian
                </a>

            </div>

        </div>

    </div>


    {{-- System Status --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <div class="flex items-center justify-between">

            <div>
                <h2 class="font-semibold text-gray-900">
                    System Status
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Status sistem aplikasi saat ini.
                </p>
            </div>

            <div class="flex items-center gap-2">

                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>

                <span class="text-sm font-medium text-green-600">
                    System Online
                </span>

            </div>

        </div>

    </div>

</div>

@endsection