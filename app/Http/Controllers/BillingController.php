<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Http\Requests\PaymentRequest;
use App\Http\Resources\BillingResource;
use App\Http\Resources\Collections\BillingCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Billing;
use App\Models\Booking;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends BaseController
{
    public function getBillings(PaginateRequest $request)
    {
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
            ->where('booking_status', '!=', Booking::STATUS_FOR_RESCHEDULE)
            ->orderBy(Billing::select('billing_status')
                ->whereColumn('booking_id', 'bookings.id')
                ->limit(1));

        $paginated = $billings->paginate(self::PER_PAGE);

        return $this->sendResponse('Billings retrieved successfully.', new BillingCollection($paginated));
    }

    public function viewBilling(int $billingId)
    {
        $billing = Booking::with('billing.latestPayment', 'billing.payments', 'customer', 'package', 'addOns')
            ->whereHas('billing', function ($query) use ($billingId) {
                $query->where('id', $billingId);
            })->first();

        if (!$billing) {
            return $this->sendError('Billing not found.', 404);
        }

        return $this->sendResponse('Billing retrieved successfully.', new BillingResource($billing));
    }

    public function addPayment(int $billingId, PaymentRequest $request)
    {
        $validated = $request->validated();
        $billing = Billing::find($billingId);

        if (!$billing) {
            return $this->sendError('Billing not found.', 404);
        }

        DB::beginTransaction();
        try {
            // Calculate payment totals
            $totalAmount = $billing->total_amount;
            $paidAmount = $billing->payments()->sum('amount_paid');
            $currentPayment = $validated['amount'];
            $newBalance = max(0, $totalAmount - ($paidAmount + $currentPayment));

            // Determine if this is the first payment
            $isFirstPayment = ($paidAmount == 0);

            // Create payment record
            $payment = Payment::create([
                'billing_id' => $billingId,
                'payment_date' => now(),
                'amount_paid' => $currentPayment,
                'payment_method' => $validated['payment_method'],
                'balance' => $newBalance,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Update billing status
            $newStatus = $this->calculateBillingStatus($totalAmount, $paidAmount + $currentPayment);
            $billing->update(['billing_status' => $newStatus]);

            // Handle booking status updates
            if ($isFirstPayment) {
                $booking = $billing->booking;

                // Update current booking to approved
                $booking->update(['booking_status' => Booking::STATUS_APPROVED]);

                // Mark conflicting bookings for reschedule
                Booking::where('booking_date', $booking->booking_date)
                    ->where('id', '!=', $booking->id)
                    ->where('booking_status', '!=', Booking::STATUS_REJECTED)
                    ->where('booking_status', '!=', Booking::STATUS_FOR_RESCHEDULE)
                    ->update(['booking_status' => Booking::STATUS_FOR_RESCHEDULE]);
            }

            DB::commit();
            return $this->sendResponse('Payment created successfully.', TransactionResource::collection(
                Payment::where('billing_id', $billingId)
                    ->orderBy('payment_date')
                    ->get()
            ));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    private function calculateBillingStatus(float $totalAmount, float $paidTotal): int
    {
        if ($paidTotal >= $totalAmount) {
            return Billing::STATUS_PAID;
        }
        return $paidTotal > 0 ? Billing::STATUS_PARTIAL : Billing::STATUS_UNPAID;
    }
}
