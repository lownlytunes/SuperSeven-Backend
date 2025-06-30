@extends('layouts.report')

@section('charts')
    <div class="flex flex-col space-y-8">
        <!-- Line Chart -->
        <div class="container mx-auto break-inside-avoid">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Number of Bookings</h2>
                    <h3 class="text-lg font-semibold">Year {{ $booking_year }}</h3>
                </div>
                <canvas id="bookingsChart" height="100"></canvas>
            </div>
        </div>


        <!-- Bar Chart -->
        <div class="container mx-auto break-inside-avoid">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Availed Packages</h2>
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold">Year {{ $package_year }}</h3>
                        @if (!empty($package_month))
                            <h3 class="text-lg font-semibold">Month of
                                {{ \Carbon\Carbon::create()->month((int) $package_month)->format('F') }}
                            </h3>
                        @endif
                    </div>
                </div>
                <canvas id="packagesChart" height="120"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('table')
    <div class="mb-4">
        <h2 class="text-lg font-semibold mb-2">Booking Transaction</h2>
        <div class="flex items-center space-x-4 mb-4">
            <h3 class="text-sm font-medium text-gray-700">Start Year: <span
                    class="font-semibold">{{ $transaction_start }}</span></h3>
            <h3 class="text-sm font-medium text-gray-700">End Year: <span
                    class="font-semibold">{{ $transaction_end }}</span></h3>
        </div>
        <table class="border-collapse w-full text-[12px]">
            <thead class="bg-gray-100">
                <tr class="text-left">
                    <th class="pb-2">Booking Date</th>
                    <th class="pb-2">Event Name</th>
                    <th class="pb-2">Client Name</th>
                    <th class="pb-2 text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($bookings as $booking)
                    <tr class="bg-white">
                        <td class="py-1">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') }}
                        </td>
                        <td class="py-1">{{ $booking->event_name }}</td>
                        <td class="py-1">
                            {{ optional($booking->customer)->full_name ?? 'N/A' }}
                        </td>
                        <td class="py-1 text-right">
                            {{ number_format($booking->billing->total_amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-2 text-gray-500">No bookings found for selected years.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    @php
        $booking_months = $monthlyData->keys()->toArray();
        $booking_values = $monthlyData->values()->toArray();

        $package_names = $packageData->pluck('package_name')->toArray();
        $package_values = $packageData->pluck('count')->toArray();
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>

        const bookingsChart = new Chart(document.getElementById('bookingsChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($booking_months) !!},
                datasets: [{
                    label: 'Bookings',
                    data: {!! json_encode($booking_values) !!},
                    fill: true,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    pointBackgroundColor: '#22c55e',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        const packagesChart = new Chart(document.getElementById('packagesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($package_names) !!},
                datasets: [{
                    label: 'Availed',
                    data: {!! json_encode($package_values) !!},
                    backgroundColor: '#22c55e'
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
@endsection