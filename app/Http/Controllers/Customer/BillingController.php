<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\BillingResource;
use App\Http\Resources\Collections\BillingCollection;
use App\Models\Billing;
use App\Models\Booking;
use Illuminate\Http\Request;

class BillingController extends BaseController
{
    public function getBillings(PaginateRequest $request)
    {
        $user = auth()->user();

        $startYear = $request->start_year;
        $endYear = $request->end_year;

        $billings = Booking::with('billing.latestPayment', 'billing.payments', 'customer', 'package', 'addOns')
        ->when(isset($request->search), function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $this->searchCallback($query, $request, ['event_name', 'customer.first_name', 'customer.last_name', 'package.package_name']);
            });
        })
        ->whereYear('booking_date', '>=', $startYear)
        ->whereYear('booking_date', '<=', $endYear)
        ->where('customer_id', $user->id)
        ->orderBy(Billing::select('billing_status')
            ->whereColumn('booking_id', 'bookings.id')
            ->limit(1));

        $paginated = $billings->paginate(self::PER_PAGE);

        return $this->sendResponse('Billings retrieved successfully.', new BillingCollection($paginated));
    }

    public function viewBilling(int $billingId)
    {
        $user = auth()->user();

        $billing = Booking::with('billing.latestPayment', 'billing.payments', 'customer', 'package', 'addOns')
            ->where('customer_id', $user->id)
            ->whereHas('billing', function ($query) use ($billingId) {
                $query->where('id', $billingId);
            })->first();

        if (!$billing) {
            return $this->sendError('Billing not found.', 404);
        }

        return $this->sendResponse('Billing retrieved successfully.', new BillingResource($billing));
    }
}
