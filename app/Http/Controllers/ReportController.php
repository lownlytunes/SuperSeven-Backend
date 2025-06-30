<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Http\Resources\Collections\ReportBookingCollection;
use App\Models\Booking;
use App\Models\Package;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getNoOfBookings(GenerateReportRequest $request)
    {
        $year = $request->input('booking_year', now()->year);

        $monthlyData = $this->reportService->generateNoOfBookings($year);

        return $this->sendResponse('Report generated successfully.', $monthlyData);
    }

    public function getNoOfPackages(GenerateReportRequest $request)
    {
        $year = $request->input('package_year', now()->year);
        $month = $request->input('package_month');

        $result = $this->reportService->generateNoOfPackages($year, $month);

        return $this->sendResponse('Report generated successfully.', $result);
    }

    public function getTransactions(GenerateReportRequest $request)
    {
        $startYear = $request->input('transaction_start', now()->year);
        $endYear = $request->input('transaction_end', now()->year);

        $bookings = $this->reportService->getTransactions($startYear, $endYear);

        $paginated = $bookings->paginate(self::PER_PAGE);

        return $this->sendResponse('Report generated successfully.', new ReportBookingCollection($paginated));
    }
}
