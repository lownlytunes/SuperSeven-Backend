<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class GenerateReportController extends BaseController
{
    private const FILE_TYPE = 'pdf';
    private const FILE_PATH = 'app/public/reports/';

    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function generatePdf(GenerateReportRequest $request)
    {
        // fetch data for report
        $monthlyData = $this->fetchNoOfBookings($request);
        $packageData = $this->fetchNoOfPackages($request);
        $addOnData = $this->fetchNoOfAddOnsPerMonth($request);
        $bookings = $this->fetchBookingTransactions($request);


        // generate pdf
        $fileName = $this->generateFileName();
        $filePath = storage_path(self::FILE_PATH . $fileName);

        // render template with data
        $template = view('generate.report', [
            'start_month_year' => Carbon::createFromFormat('Y-m', $request->input('start_month_year'))->format('F Y'),
            'end_month_year' => Carbon::createFromFormat('Y-m', $request->input('end_month_year'))->format('F Y'),
            'billing_start' => $request->input('billing_start'),
            'billing_end' => $request->input('billing_end'),
            'monthlyData' => $monthlyData,
            'packagesChart' => $packageData,
            'addonsChart' => $addOnData,
            'bookings' => $bookings,
        ])->render();

        // generate pdf using browsershot
        Browsershot::html($template)
            ->showBackground()
            ->margins(10, 4, 12.7, 4)
            ->format('legal')
            ->waitUntilNetworkIdle()
            ->savePdf($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    private function fetchNoOfBookings(GenerateReportRequest $request)
    {
        // Expect month-year format like '2024-01' for January 2024
        $startMonthYear = $request->input('start_month_year', now()->startOfYear()->format('Y-m'));
        $endMonthYear = $request->input('end_month_year', now()->endOfYear()->format('Y-m'));

        $monthlyData = $this->reportService->generateNoOfBookings($startMonthYear, $endMonthYear);

        return $monthlyData;
    }

    private function fetchNoOfPackages(GenerateReportRequest $request)
    {
        // Use month-year format
        $startMonthYear = $request->input('start_month_year', now()->startOfYear()->format('Y-m'));
        $endMonthYear = $request->input('end_month_year', now()->endOfYear()->format('Y-m'));

        $result = $this->reportService->generatePackageTotals($startMonthYear, $endMonthYear);

        return $result;
    }

    private function fetchNoOfAddOnsPerMonth(GenerateReportRequest $request)
    {
        $startMonthYear = $request->input('start_month_year', now()->startOfYear()->format('Y-m'));
        $endMonthYear   = $request->input('end_month_year', now()->endOfYear()->format('Y-m'));

        $addOnData = $this->reportService->generateAddOnTotals($startMonthYear, $endMonthYear);

        return $addOnData;
    }

    private function fetchBookingTransactions(GenerateReportRequest $request)
    {
        $startYear = $request->input('transaction_start', now()->year);
        $endYear = $request->input('transaction_end', now()->year);

        $bookings = $this->reportService->getTransactions($startYear, $endYear);

        return $bookings->get();
    }

    private function generateFileName(): string
    {
        $timestamp = now()->format('Ymd_His');
        $randomString = Str::random(8);
        return "report_{$timestamp}_{$randomString}." . self::FILE_TYPE;
    }
}