<?php

namespace App\Services;

use App\Models\AddOn;
use App\Models\Billing;
use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function generateNoOfBookings(?string $startMonthYear, ?string $endMonthYear)
    {
        // Create Carbon instances for the first day of start month and last day of end month
        $startDate = Carbon::createFromFormat('Y-m', $startMonthYear)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $endMonthYear)->endOfMonth();

        // Get bookings grouped by year and month
        $bookings = Booking::query()
            ->selectRaw('YEAR(booking_date) as year, MONTH(booking_date) as month, COUNT(*) as count')
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->whereHas('billing', function ($query) {
                $query->where('billing_status', Billing::STATUS_PAID);
            })
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->groupByRaw('YEAR(booking_date), MONTH(booking_date)')
            ->orderByRaw('YEAR(booking_date), MONTH(booking_date)')
            ->get();

        // Create a collection with all months in the range
        $data = collect();
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $key = $current->format('F Y');

            // Find booking count for this specific month
            $booking = $bookings->first(function ($item) use ($current) {
                return $item->year == $current->year && $item->month == $current->month;
            });

            $data[$key] = $booking ? $booking->count : 0;
            $current->addMonth();
        }

        return $data;
    }

    public function generatePackageTotals(?string $startMonthYear, ?string $endMonthYear)
    {
        $start = Carbon::createFromFormat('Y-m', $startMonthYear ?? now()->startOfYear()->format('Y-m'))
            ->startOfMonth();

        $end = Carbon::createFromFormat('Y-m', $endMonthYear ?? now()->endOfYear()->format('Y-m'))
            ->endOfMonth();

        $packages = Package::where('status', Package::STATUS_ACTIVE)
            ->withCount(['bookings as bookings_count' => function ($q) use ($start, $end) {
                $q->where('booking_status', Booking::STATUS_APPROVED)
                    ->whereBetween('booking_date', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString()
                    ])
                    ->whereHas('billing', function ($qb) {
                        $qb->where('billing_status', Billing::STATUS_PAID);
                    });
            }])
            ->orderByDesc('bookings_count')
            ->get();

        // Build chart-ready payload
        $labels = [];
        $values = [];
        $backgroundColors = [];
        $borderColors = [];

        foreach ($packages as $package) {
            // Skip packages with zero bookings if desired
            if ($package->bookings_count === 0) {
                continue;
            }

            $labels[] = $package->package_name;
            $values[] = (int) $package->bookings_count;

            // Deterministic color per package
            $color = $this->packageColor($package->id);

            $backgroundColors[] = $color;
            $borderColors[] = $color;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'backgroundColors' => $backgroundColors,
            'borderColors' => $borderColors,
        ];
    }

    public function generateAddOnTotals(?string $startMonthYear, ?string $endMonthYear): array
    {
        $start = Carbon::createFromFormat('Y-m', $startMonthYear ?? now()->startOfYear()->format('Y-m'))
            ->startOfMonth();

        $end = Carbon::createFromFormat('Y-m', $endMonthYear ?? now()->endOfYear()->format('Y-m'))
            ->endOfMonth();

        $addOns = AddOn::query()
            ->withCount(['bookings as bookings_count' => function ($q) use ($start, $end) {
                $q->where('booking_status', Booking::STATUS_APPROVED)
                    ->whereBetween('booking_date', [
                        $start->toDateTimeString(),
                        $end->toDateTimeString(),
                    ])
                    ->whereHas('billing', function ($qb) {
                        $qb->where('billing_status', Billing::STATUS_PAID);
                    });
            }])
            ->orderByDesc('bookings_count')
            ->get();

        $labels = [];
        $values = [];
        $backgroundColors = [];
        $borderColors = [];

        foreach ($addOns as $addOn) {
            // optional: skip unused add-ons
            if ($addOn->bookings_count === 0) {
                continue;
            }

            $labels[] = $addOn->add_on_name; // or $addOn->addon_name
            $values[] = (int) $addOn->bookings_count;

            $color = $this->addOnColor($addOn->id);

            $backgroundColors[] = $color;
            $borderColors[] = $color;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'backgroundColors' => $backgroundColors,
            'borderColors' => $borderColors,
        ];
    }

    public function getTransactions(string $startYear, string $endYear)
    {
        $bookings = Booking::with('customer', 'package', 'addOns', 'billing')
            ->whereBetween('booking_date', [
                $startYear . '-01-01 00:00:00',
                $endYear . '-12-31 23:59:59'
            ])
            ->where('booking_status', Booking::STATUS_APPROVED)
            // ->where('deliverable_status', Booking::STATUS_COMPLETED)
            ->whereHas('billing', function ($query) {
                $query->where('billing_status', Billing::STATUS_PAID);
            })
            ->orderBy('booking_date', 'asc');

        return $bookings;
    }

    private function packageColor(int $packageId): string
    {
        $hue = ($packageId * 47) % 360;
        return "hsl({$hue}, 65%, 55%)";
    }

    private function addOnColor(int $addOnId): string
    {
        $hue = ($addOnId * 53) % 360;
        return "hsl({$hue}, 70%, 55%)";
    }
}