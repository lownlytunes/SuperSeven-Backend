<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\PaginateRequest;
use App\Http\Requests\Customer\AddFeedbackRequest;
use App\Http\Requests\Customer\CreateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\FeedbackResource;
use App\Models\Booking;
use App\Models\Feedback;
use App\Services\BookingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }


    public function getBookings(PaginateRequest $request)
    {
        $user = auth()->user();

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $bookings = Booking::with('customer', 'package', 'addOns')
            ->where('customer_id', $user->id)
            ->whereMonth('booking_date', $month)
            ->whereYear('booking_date', $year)
            ->orderBy('booking_date')
            ->orderBy('created_at')
            ->get();

        return $this->sendResponse('Bookings retrieved successfully.', BookingResource::collection($bookings));
    }

    public function viewBooking(int $bookingId)
    {
        $user = auth()->user();

        $booking = Booking::with('customer', 'package', 'addOns')
            ->where('id', $bookingId)
            ->where('customer_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        return $this->sendResponse('Booking retrieved successfully.', new BookingResource($booking));
    }

    public function createBooking(CreateBookingRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        DB::beginTransaction();
        try {

            // calculate discount
            $discount = $this->bookingService->getDiscountPercentage($validated['booking_date']);

            // create booking
            $booking = Booking::create([
                'customer_id' => $user->id,
                'booking_date' => $validated['booking_date'],
                'ceremony_time' => $validated['ceremony_time'],
                'package_id' => $validated['package_id'],
                'event_name' => $validated['event_name'],
                'booking_address' => $validated['booking_address'],
                'category' => $validated['category'],
                'booking_status' => Booking::STATUS_PENDING,
                'deliverable_status' => Booking::STATUS_UNASSIGNED,
                'discount' => $discount
            ]);

            $addOnIds = (array) $request->input('addon_id', []);

            $booking->addons()->sync($addOnIds);

            $this->bookingService->createBillingStatement(
                $booking->id,
                $validated['package_id'],
                $addOnIds,
                $discount
            );

            $this->bookingService->sendReceivedMailToAdmin($booking);

            $this->bookingService->sendSuccessMailToCustomer($booking);

            DB::commit();
            return $this->sendResponse('Booking created successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function updateBooking(int $id, UpdateBookingRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        $booking = Booking::where('id', $id)
            ->where('customer_id', $user->id)
            ->first();

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->booking_date = $validated['booking_date'];
            $booking->ceremony_time = $validated['ceremony_time'];
            $booking->event_name = $validated['event_name'];
            $booking->booking_address = $validated['booking_address'];
            $booking->category = $validated['category'];
            $booking->package_id = $validated['package_id'];

            $shouldUpdateBilling = false;
            if ($booking->isDirty('booking_date')) {
                $booking->discount = $this->bookingService
                    ->getDiscountPercentage($validated['booking_date']);
                $shouldUpdateBilling = true;
            }

            $currentAddOns = $booking->addons()->pluck('add_on_id')->toArray();
            $newAddOns = (array) $request->input('addon_id', []);
            $addOnsChanged = count(array_diff($currentAddOns, $newAddOns)) > 0
                || count(array_diff($newAddOns, $currentAddOns)) > 0;

            $packageChanged = $booking->isDirty('package_id');

            if ($packageChanged || $addOnsChanged || $shouldUpdateBilling) {
                if ($addOnsChanged) {
                    $booking->addons()->sync($newAddOns);
                }

                $this->bookingService->updateBillingStatement(
                    $booking->id,
                    $validated['package_id'],
                    $newAddOns,
                    $booking->discount
                );
            }

            $booking->save();
            DB::commit();
            return $this->sendResponse('Booking updated successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }


    public function cancelBooking(int $bookingId)
    {
        $user = auth()->user();

        $booking = Booking::where('id', $bookingId)
            ->where('customer_id', $user->id)
            ->where('booking_status', '!=', Booking::STATUS_APPROVED)
            ->first();

        if (!$booking) {
            return $this->sendError('Booking not found or already approved.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->deleted_by = $user->full_name;
            $booking->save();
            $booking->delete();
            $booking->billing()->delete();

            DB::commit();

            $deletedBooking = Booking::where('id', $bookingId)->withTrashed()->first();
            $this->bookingService->sendCancellationMail($deletedBooking);

            return $this->sendResponse('Booking deleted successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function viewFeedback(int $bookingId)
    {
        $feedback = Feedback::where('booking_id', $bookingId)->first();

        if (!$feedback) {
            return $this->sendError('Feedback not found.', 404);
        }

        return $this->sendResponse('Feedback retrieved successfully.', new FeedbackResource($feedback));
    }

    public function addFeedback(int $bookingId, AddFeedbackRequest $request)
    {
        $user = auth()->user();

        $validated = $request->validated();

        $booking = Booking::where('id', $bookingId)->first();

        if (!$booking) {
            return $this->sendError('Booking not found or not completed.', 404);
        }

        DB::beginTransaction();
        try {

            $feedback = Feedback::create([
                'user_id'=> $user->id,
                'booking_id' => $booking->id,
                'feedback_date' => now(),
                'feedback_details' => $validated['feedback_details'],
                'feedback_status' => Feedback::STATUS_PENDING
            ]);
            
            DB::commit();
            return $this->sendResponse('Feedback added successfully.', new FeedbackResource(Feedback::find($feedback->id)));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }
}
