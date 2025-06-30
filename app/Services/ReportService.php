<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;

class ReportService
{
    public function generateNoOfBookings(string $year)
    {
        $bookings = Booking::query()
            ->selectRaw('MONTH(booking_date) as month, COUNT(*) as count')
            ->whereYear('booking_date', $year)
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->where('deliverable_status', Booking::STATUS_COMPLETED)
            ->groupByRaw('MONTH(booking_date)')
            ->orderByRaw('MONTH(booking_date)')
            ->pluck('count', 'month');

        $data = collect(range(1, 12))->mapWithKeys(function ($month) use ($bookings) {
            $monthName = Carbon::create()->month($month)->format('F');
            return [$monthName => $bookings->get($month, 0)];
        });
        
        return $data;
    }

    public function generateNoOfPackages(?string $year, ?string $month)
    {
        $packages = Package::withCount([
            'bookings as bookings_count' => function ($query) use ($year, $month) {
                $query->where('booking_status', Booking::STATUS_APPROVED);
                $query->where('deliverable_status', Booking::STATUS_COMPLETED);
    
                if ($year) {
                    $query->whereYear('booking_date', $year);
                }
    
                if ($month) {
                    $query->whereMonth('booking_date', $month);
                }
            }
        ])->get();

        return $packages->map(function ($package) {
            return [
                'package_name' => $package->package_name,
                'count' => $package->bookings_count,
            ];
        });
    }

    public function getTransactions(string $startYear, string $endYear)
    {
        $bookings = Booking::with('customer', 'package', 'addOns', 'billing')
            ->whereBetween('booking_date', [
                $startYear . '-01-01 00:00:00',
                $endYear . '-12-31 23:59:59'
            ])
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->where('deliverable_status', Booking::STATUS_COMPLETED)
            ->orderBy('booking_date', 'asc');
    
        return $bookings;
    }
}