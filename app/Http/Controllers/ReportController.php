<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Http\Resources\Collections\ReportBillingCollection;
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
        $startMonthYear = $request->input('start_month_year', now()->startOfYear()->format('Y-m'));
        $endMonthYear = $request->input('end_month_year', now()->endOfYear()->format('Y-m'));

        $monthlyData = $this->reportService->generateNoOfBookings($startMonthYear, $endMonthYear);

        return $this->sendResponse('Report generated successfully.', $monthlyData);
    }

    public function getNoOfPackages(GenerateReportRequest $request)
    {
        $startMonthYear = $request->input('start_month_year', now()->startOfYear()->format('Y-m'));
        $endMonthYear = $request->input('end_month_year', now()->endOfYear()->format('Y-m'));

        $result = $this->reportService->generatePackageTotals($startMonthYear, $endMonthYear);

        return $this->sendResponse('Report generated successfully.', $result);
    }

    public function getNoOfAddOns(GenerateReportRequest $request)
    {
        $startMonthYear = $request->input('start_month_year', now()->startOfYear()->format('Y-m'));
        $endMonthYear = $request->input('end_month_year', now()->endOfYear()->format('Y-m'));

        $result = $this->reportService->generateAddOnTotals($startMonthYear, $endMonthYear);

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

    public function getBillingInformation(GenerateReportRequest $request)
    {
        $startYear = $request->input('billing_start', now()->year);
        $endYear = $request->input('billing_end', now()->year);

        $bookings = $this->reportService->getTransactions($startYear, $endYear);

        $paginated = $bookings->paginate(self::PER_PAGE);

        return $this->sendResponse('Report generated successfully.', new ReportBillingCollection($paginated));
    }
}