@extends('layouts.report')

@section('charts')
    <div class="flex flex-col space-y-8">
        <!-- No of Booking Line Chart -->
        <div class="break-inside-avoid">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Number of Bookings</h2>
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold">{{ $start_month_year }} to {{ $end_month_year }}</h3>
                </div>
                </div>
                <canvas id="bookingsChart" height="100"></canvas>
            </div>
        </div>

        <!-- No Availed Packages Bar Chart -->
        <div class="break-inside-avoid">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Availed Packages</h2>
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold">{{ $start_month_year }} to {{ $end_month_year }}</h3>
                    </div>
                </div>
                <canvas id="packagesChart" height="120"></canvas>
            </div>
        </div>

        <!-- No of Add-Ons Bar Chart -->
        <div class="break-inside-avoid">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold">Availed Add-Ons</h2>
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold">{{ $start_month_year }} to {{ $end_month_year }}</h3>
                    </div>
                </div>
                <canvas id="addonsChart" height="100"></canvas>
            </div>
        </div>
    </div>
@endsection

@section('booking_table')
    <div class="section-content">
        <h2 class="section-title">Booking Information</h2>
        <div class="section-meta">
            <span>Start Year: <span class="font-semibold">{{ $start_month_year }}</span></span>
            <span>End Year: <span class="font-semibold">{{ $end_month_year }}</span></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Event Date</th>
                    <th style="width: 28%;">Event Name</th>
                    <th style="width: 22%;">Client Name</th>
                    <th style="width: 18%;">Package</th>
                    <th style="width: 14%; text-align: right;">Category</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') }}</td>
                        <td>{{ $booking->event_name }}</td>
                        <td>{{ optional($booking->customer)->full_name ?? 'N/A' }}</td>
                        <td>{{ optional($booking->package)->package_name ?? 'N/A' }}</td>
                        <td style="text-align: right;">{{ $booking->event_category }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px; color: #9ca3af;">
                            No bookings found for selected years.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('billing_table')
        <div class="section-content">
        <h2 class="section-title">Billing Information</h2>
        <div class="section-meta">
            <span>Start Year: <span class="font-semibold">{{ $start_month_year }}</span></span>
            <span>End Year: <span class="font-semibold">{{ $end_month_year }}</span></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Event Date</th>
                    <th style="width: 28%;">Event Name</th>
                    <th style="width: 22%;">Client Name</th>
                    <th style="width: 18%;">Status</th>
                    <th style="width: 18%;">Total Amount</th>
                    <th style="width: 14%; text-align: right;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F j, Y') }}</td>
                        <td>{{ $booking->event_name }}</td>
                        <td>{{ optional($booking->customer)->full_name ?? 'N/A' }}</td>
                        <td>{{ $booking->billing->status_label }}</td>
                        <td>{{ $booking->billing->total_amount }}</td>
                        <td style="text-align: right;">{{ $booking->billing->balance }}</td>
                    </tr>
                @empty
                    <tr>    
                        <td colspan="5" style="text-align: center; padding: 20px; color: #9ca3af;">
                            No Billing found for selected years.
                        </td>
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

        $package_data = $packagesChart;
        $addon_data = $addonsChart;
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const bookingsChart = new Chart(document.getElementById('bookingsChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($booking_months) !!},
                datasets: [{
                    label: 'Bookings per Month',
                    data: {!! json_encode($booking_values) !!},
                    fill: true,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    pointBackgroundColor: '#22c55e',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        position: 'bottom',
                     },
                    tooltip: { enabled: true },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        const addonsChart = new Chart(document.getElementById('addonsChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($addon_data['labels']) !!},
                datasets: [{
                    label: 'No of Availed Add-Ons',
                    data: {!! json_encode($addon_data['values']) !!},
                    backgroundColor: {!! json_encode($addon_data['backgroundColors']) !!},
                    borderColor: {!! json_encode($addon_data['borderColors']) !!},
                    borderWidth: 1
                }]
            },
            options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        boxWidth: 0,
                    }
                },
                tooltip: { enabled: true }
            },
                 scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
        }
        });

        const packagesChart = new Chart(document.getElementById('packagesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($package_data['labels']) !!},
                datasets: [{
                    label: 'No of Availed Packages',
                    data: {!! json_encode($package_data['values']) !!},
                    backgroundColor: {!! json_encode($package_data['backgroundColors']) !!},
                    borderColor: {!! json_encode($package_data['borderColors']) !!},
                    borderWidth: 1
                }]
            },
            options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        boxWidth: 0,
                    }
                },
                tooltip: { enabled: true }
            },
                 scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
        }
        });
    </script>
@endsection