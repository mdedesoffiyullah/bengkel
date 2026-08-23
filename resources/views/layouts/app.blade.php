<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Bengkel Management System' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-900">

    <div class="min-h-screen flex">

        {{-- =========================================================
             SIDEBAR
        ========================================================== --}}
        <aside class="w-64 bg-slate-900 text-white flex-shrink-0">

            {{-- BRAND --}}
            <div class="h-16 flex items-center px-6 border-b border-slate-700">

                <div>
                    <h1 class="text-lg font-bold">
                        BENGKEL
                    </h1>

                    <p class="text-xs text-slate-400">
                        Management System
                    </p>
                </div>

            </div>


            {{-- NAVIGATION --}}
            <nav class="p-4 space-y-1">

                {{-- =================================================
                     DASHBOARD
                ================================================== --}}

                <a href="{{ route('dashboard') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Dashboard
                </a>


                {{-- =================================================
                     MASTER DATA
                ================================================== --}}

                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase">
                        Master Data
                    </p>
                </div>


                                {{-- Customers --}}
                <a
                    href="{{ route('customers.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100"
                >
                    Customers
                </a>

                {{-- Suppliers --}}
                <a
                    href="{{ route('suppliers.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100"
                >
                    Suppliers
                </a>

                {{-- Products --}}
                <a
                    href="{{ route('products.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100"
                >
                    Products
                </a>

                {{-- Services --}}
                <a
                    href="{{ route('services.index') }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100"
                >
                    Services
                </a>


                {{-- =================================================
                     OPERATIONAL
                ================================================== --}}

                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase">
                        Operational
                    </p>
                </div>


                {{-- Work Orders --}}
                <a href="{{ route('work-orders.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Work Orders
                </a>


                {{-- Purchases --}}
                <a href="{{ route('purchases.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Purchases
                </a>


                {{-- Inventory --}}
                <a href="{{ route('inventory-balances.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Inventory
                </a>


                {{-- Stock Opname --}}
                <a href="{{ route('stock-opnames.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Stock Opname
                </a>


                {{-- =================================================
                     FINANCE
                ================================================== --}}

                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase">
                        Finance
                    </p>
                </div>                
                {{-- Invoices --}}
                {{-- 
                <a href="{{ route('invoices.index') }}"
                    class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Invoices
                </a>
                --}}


                {{-- Payments --}}
                <a href="{{ route('payments.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Payments
                </a>


                {{-- Profit & Loss --}}
                <a href="{{ route('profit-loss.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Laba & Rugi
                </a>

                {{-- Expenses --}}
                <a href="{{ route('expenses.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Expenses
                </a>


                {{-- =================================================
                     SUPPORT
                ================================================== --}}

                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase">
                        Support
                    </p>
                </div>


                {{-- Complaints --}}
                <a href="{{ route('complaints.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg hover:bg-slate-800 transition">
                    Complaints
                </a>

            </nav>

        </aside>


        {{-- =========================================================
             MAIN CONTENT
        ========================================================== --}}
        <div class="flex-1 flex flex-col min-w-0">


            {{-- =====================================================
                 TOPBAR
            ====================================================== --}}
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">

                <div>

                    <h2 class="text-lg font-semibold">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </h2>

                </div>


                {{-- ADMINISTRATOR --}}
                <div class="flex items-center gap-3">

                    <div class="text-right">

                        <p class="text-sm font-medium">
                            Administrator
                        </p>

                        <p class="text-xs text-gray-500">
                            Administrator
                        </p>

                    </div>


                    <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-semibold">
                        A
                    </div>

                </div>

            </header>


            {{-- =====================================================
                 PAGE CONTENT
            ====================================================== --}}
            <main class="flex-1 p-6">

                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>





