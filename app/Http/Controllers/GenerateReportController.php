<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateReportRequest;
use App\Services\ReportService;
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
        $bookings = $this->fetchBookingTransactions($request);

        // generate pdf
        $fileName = $this->generateFileName();
        $filePath = storage_path(self::FILE_PATH . $fileName);

        // render template with data
        $template = view('generate.report', [
            'booking_year' => $request->input('booking_year', now()->year),
            'package_year' => $request->input('package_year'),
            'package_month' => $request->input('package_month'),
            'transaction_start' => $request->input('transaction_start'),
            'transaction_end' => $request->input('transaction_end'),
            'monthlyData' => $monthlyData,
            'packageData' => $packageData,
            'bookings' => $bookings
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
        $year = $request->input('booking_year', now()->year);

        $monthlyData = $this->reportService->generateNoOfBookings($year);

        return $monthlyData;
    }

    private function fetchNoOfPackages(GenerateReportRequest $request)
    {
        $year = $request->input('package_year', now()->year);
        $month = $request->input('package_month');

        $result = $this->reportService->generateNoOfPackages($year, $month);

        return $result;
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
