<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnavailableDateResource;
use App\Models\UnavailableDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DateController extends BaseController
{

    public function getUnavailableDate(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $unavailableDates = UnavailableDate::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        return $this->sendResponse('Unavailable dates retrieved successfully.', UnavailableDateResource::collection($unavailableDates));
    }

    public function markUnavailableDate(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->validate([
                'date' => "required|date|unique:unavailable_dates,date|after_or_equal:today",
                'reason' => "nullable|string",
            ]);

            $unavailableDate = UnavailableDate::create([
                'date' => $request->date,
                'reason' => $request->reason,
                'created_by' => auth()->user()->first_name,
            ]);

            DB::commit();
            return $this->sendResponse('Unavailable date created successfully.', new UnavailableDateResource($unavailableDate));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function unmarkUnavailableDate(int $id)
    {
        $unavailableDate = UnavailableDate::find($id);
        if (!$unavailableDate) {
            return $this->sendError('Date not found.', 404);
        }

        DB::beginTransaction();
        try {

            $unavailableDate->delete();

            DB::commit();
            return $this->sendResponse('Unavailable date unmarked successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
