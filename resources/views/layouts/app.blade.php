<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style id="tambalan-bg-build">
            /* Tambalan: class bg-* berikut hilang dari CSS hasil build. */
            .bg-amber-50{background-color:#fffbeb}.bg-blue-50{background-color:#eff6ff}
            .bg-blue-100{background-color:#dbeafe}.bg-blue-600{background-color:#2563eb}
            .bg-emerald-50{background-color:#ecfdf5}.bg-emerald-600{background-color:#059669}
            .bg-gray-300{background-color:#d1d5db}.bg-gray-400{background-color:#9ca3af}
            .bg-gray-600{background-color:#4b5563}.bg-green-50{background-color:#f0fdf4}
            .bg-green-500{background-color:#22c55e}.bg-indigo-600{background-color:#4f46e5}
            .bg-purple-50{background-color:#faf5ff}.bg-purple-600{background-color:#9333ea}
            .bg-red-100{background-color:#fee2e2}.bg-teal-50{background-color:#f0fdfa}
            .bg-teal-100{background-color:#ccfbf1}.bg-teal-600{background-color:#0d9488}
            .bg-yellow-50{background-color:#fefce8}
            .hover\:bg-blue-700:hover{background-color:#1d4ed8}
            .hover\:bg-emerald-700:hover{background-color:#047857}
            .hover\:bg-gray-300:hover{background-color:#d1d5db}
            .hover\:bg-indigo-700:hover{background-color:#4338ca}
            .hover\:bg-purple-700:hover{background-color:#7e22ce}
            .hover\:bg-teal-700:hover{background-color:#0f766e}
        </style>
        <style id="konsistensi-form-surat">
            /* Konsistensi tampilan form Buat Surat (khusus #letter_form) */
            #letter_form .bg-blue-50, #letter_form .bg-emerald-50, #letter_form .bg-purple-50,
            #letter_form .bg-teal-50, #letter_form .bg-teal-100, #letter_form .bg-indigo-50,
            #letter_form .bg-green-50, #letter_form .bg-gray-50 {
                background-color: #f8fafc;
            }
            #letter_form .border-teal-300, #letter_form .border-teal-200,
            #letter_form .border-emerald-200, #letter_form .border-blue-200,
            #letter_form .border-purple-200, #letter_form .border-purple-300 {
                border-color: #e2e8f0;
            }
            #letter_form .text-teal-800, #letter_form .text-purple-800,
            #letter_form .text-emerald-800, #letter_form .text-blue-800,
            #letter_form .text-teal-700, #letter_form .text-purple-700,
            #letter_form .text-emerald-700, #letter_form .text-blue-700 {
                color: #1e293b;
            }
            #letter_form label { color: #334155; }
            #letter_form input:not([type="checkbox"]):not([type="radio"]),
            #letter_form select, #letter_form textarea {
                border-color: #cbd5e1; border-radius: 0.375rem;
            }
            #letter_form input:focus, #letter_form select:focus, #letter_form textarea:focus {
                border-color: #6366f1; outline: none;
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
            }
            #letter_form #btn_add_mmo_debtor, #letter_form #btn_open_debtor_modal,
            #letter_form #btn_add_tb1_row, #letter_form #btn_add_tb2_row,
            #letter_form .btn-add-related {
                background-color: #4f46e5; color: #ffffff;
            }
            #letter_form #btn_add_mmo_debtor:hover, #letter_form #btn_open_debtor_modal:hover,
            #letter_form #btn_add_tb1_row:hover, #letter_form #btn_add_tb2_row:hover,
            #letter_form .btn-add-related:hover {
                background-color: #4338ca;
            }
            #letter_form .btn-remove-mmo-row, #letter_form .btn-remove-rel,
            #letter_form .btn-remove-debtor, #letter_form .btn-remove-bb-row {
                background-color: transparent; color: #dc2626; font-weight: 600;
            }
            #letter_form table th { background-color: #f1f5f9; color: #334155; }
            #letter_form table th, #letter_form table td { border-color: #e2e8f0; }
            #letter_form .select2-container .select2-selection--single {
                border-color: #cbd5e1; border-radius: 0.375rem;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
