<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Report' }}</title>
    @vite('resources/css/app.css')
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 14px;
        color: #374151;
    }

    /* Page break classes */
    .page-break {
        page-break-before: always;
        break-before: page;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }

    .page-break-first {
        page-break-before: avoid;
    }

    /* Prevent breaks inside tables */
    table {
        page-break-inside: auto;
    }

    tbody tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* Keep headers with content */
    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    /* Section spacing */
    .section-content {
        page-break-inside: avoid;
    }

    .charts-section {
        page-break-after: always;
    }

    .booking-section {
        page-break-after: always;
    }

    /* Table styling */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    thead th {
        background-color: #f3f4f6;
        padding: 10px 12px;
        font-weight: 600;
        border-bottom: 2px solid #d1d5db;
        text-align: left;
        font-size: 12px;
    }

    tbody td {
        padding: 8px 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 12px;
    }

    tbody tr:last-child td {
        border-bottom: 1px solid #d1d5db;
    }

    tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Section styling */
    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #111827;
    }

    .section-meta {
        display: flex;
        gap: 20px;
        margin-bottom: 16px;
        font-size: 12px;
    }

    .section-meta span {
        color: #6b7280;
    }

    .section-meta .font-semibold {
        color: #111827;
        font-weight: 600;
    }
</style>
</head>
<body class="antialiased">
    {{-- Header --}}
    <div class="w-full bg-gradient-to-t from-slate-200 via-white">
        <div class="container flex justify-between w-full mx-auto p-8">
            <div class="flex flex-col justify-between w-5/12">
                {{-- Company Info --}}
                <div class="flex flex-col gap-y-4">
                    <span class="flex flex-col font-bold">
                        <span class="text-base">{{ config('app.name') }}</span>
                        <span class="font-normal text-[10px] text-gray-500">Crafting Superb Moments</span>
                    </span>
                </div>
            </div>
            <div class="flex flex-col justify-between text-[10px]">
                <div class="flex">
                    <span class="font-light">Date Generated: {{ now()->format('l, F j, Y') }}</span>
                </div>
                <div class="flex flex-col">
                    <p>Address: {{ config('mail.contact.address') }}</p>
                    <p>Phone: {{ config('mail.contact.phone') }}</p>
                    <p>Email: {{ config('mail.contact.email') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section (Page 1) --}}
    <div class="container mx-auto p-8 charts-section">
        @yield('charts')
    </div>

    {{-- Booking Table Section (Page 2) --}}
    <div class="page-break booking-section">
        <div class="container mx-auto p-8">
            @yield('booking_table')
        </div>
    </div>

    {{-- Billing Table Section (Page 3) --}}
    <div class="page-break">
        <div class="container mx-auto p-8">
            @yield('billing_table')
        </div>
    </div>

    @yield('scripts')
</body>
</html>