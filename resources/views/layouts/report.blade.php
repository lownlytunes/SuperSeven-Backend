<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Report' }}</title>
    @vite('resources/css/app.css')
</head>
<body class="antialiased flex flex-col h-screen text-sm font-sans text-gray-700 tracking-tight">
    {{-- Header --}}
    <div class="w-full bg-gradient-to-t from-slate-200 via-white">
        <div class="container flex justify-between w-full mx-auto p-8">
            <div class="flex flex-col justify-between w-5/12">
                {{-- Logo --}}
                <div class="mb-2"></div>
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
                    <span class="font-light">Date Generate: {{ now()->format('l, F j, Y') }}</span>
                </div>
                <div class="flex flex-col">
                    <p>Address: {{ config('mail.contact.address') }}</p>
                    <p>Phone: {{ config('mail.contact.phone') }}</p>
                    <p>Email: {{ config('mail.contact.email') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="container mx-auto p-8">
        @yield('charts')
    </div>

    <div class="container mx-auto p-8">
        @yield('table')
    </div>

    @yield('scripts')
</body>
</html>
