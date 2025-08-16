<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddBookingRequest;
use App\Http\Requests\Booking\PaginateRequest;
use App\Http\Requests\ReschedBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingAddOnsResource;
use App\Http\Resources\Collections\BookingCollection;
use App\Models\Booking;
use \App\Models\Package;
use \App\Models\AddOn;
use App\Services\BookingService;
use Carbon\Carbon;
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
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $bookings = Booking::with('customer', 'package', 'addOns')
            ->when(isset($request->filters), function ($query) use ($request) {
                $query->where(function ($subquery) use ($request) {
                    $this->filterCallback($subquery, $request, $this->bookingService->getFilterBookingData());
                });
            })
            ->whereMonth('booking_date', $month)
            ->whereYear('booking_date', $year)
            ->orderBy('booking_date')
            ->orderBy('created_at')
            ->get();

        return $this->sendResponse('Bookings retrieved successfully.', new BookingCollection($bookings));
    }

    public function viewBooking(int $bookingId)
    {
        $booking = Booking::with('customer', 'package', 'addOns')->find($bookingId);

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        return $this->sendResponse('Booking retrieved successfully.', new BookingResource($booking));
    }

    public function getApprovedBookings()
    {
        $bookings = Booking::with('customer', 'package', 'addOns')
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->orderBy('booking_date')
            ->orderBy('created_at')
            ->get();

        return $this->sendResponse('Bookings retrieved successfully.',  BookingResource::collection($bookings));
    }

    public function addBooking(AddBookingRequest $request)
    {
        $request->validated();

        DB::beginTransaction();
        try {

            //create walk in customer account
            $customer = $this->bookingService->createWalkinCustomer($request->first_name, $request->last_name, $request->address, $request->email, $request->contact_no);

            // calculate discount
            $discount = $this->bookingService->getDiscountPercentage($request->booking_date);

            // create booking
            $booking = Booking::create([
                'booking_date' => $request->booking_date,
                'ceremony_time' => $request->ceremony_time,
                'customer_id' => $customer->id,
                'package_id' => $request->package_id,
                'event_name' => $request->event_name,
                'booking_address' => $request->booking_address,
                'completion_date' => $request->completion_date,
                'booking_status' => Booking::STATUS_PENDING,
                'deliverable_status' => Booking::STATUS_UNASSIGNED,
                'discount' => $discount,
            ]);

            $addOnIds = (array) $request->input('addon_id', []);

            $booking->addons()->sync($addOnIds);

            $this->bookingService->createBillingStatement(
                $booking->id,
                $request->package_id,
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

    public function updateBooking(int $bookingId, UpdateBookingRequest $request)
    {
        $request->validated();

        $existingBooking = Booking::where('id', '!=', $bookingId)
            ->where('booking_date', $request->booking_date)
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->first();

        if ($existingBooking) {
            return $this->sendError('Booking already exists for this date.', 400);
        }

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {
            // Update basic fields
            $booking->fill([
                'booking_date' => $request->booking_date,
                'ceremony_time' => $request->ceremony_time,
                'event_name' => $request->event_name,
                'booking_address' => $request->booking_address,
            ]);

            // Check for discount changes
            $shouldUpdateBilling = false;
            if ($booking->isDirty('booking_date')) {
                $booking->discount = $this->bookingService
                    ->getDiscountPercentage($request->booking_date);
                $shouldUpdateBilling = true;
            }

            // Check for package/add-on changes
            $addOnIds = (array) $request->input('addon_id', []);
            $currentAddOns = $booking->addons()->pluck('add_on_id')->toArray();

            $packageChanged = $booking->package_id != $request->package_id;
            $addOnsChanged = count(array_diff($currentAddOns, $addOnIds)) > 0
                || count(array_diff($addOnIds, $currentAddOns)) > 0;

            if ($packageChanged || $addOnsChanged || $shouldUpdateBilling) {
                $booking->package_id = $request->package_id;
                $booking->addons()->sync($addOnIds);

                $this->bookingService->updateBillingStatement(
                    $booking->id,
                    $request->package_id,
                    $addOnIds,
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
        $booking = Booking::where('id', $bookingId)
            ->where('booking_status', '!=', Booking::STATUS_APPROVED)
            ->first();

        if (!$booking) {
            return $this->sendError('Booking not found or already approved.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->deleted_by = auth()->user()->full_name;
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

    public function setBookingToApprove(int $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->booking_status = Booking::STATUS_APPROVED;
            $booking->save();

            Booking::where('booking_date', $booking->booking_date)
                ->where('booking_status', Booking::STATUS_PENDING)
                ->where('id', '!=', $bookingId)
                ->update(['booking_status' => Booking::STATUS_FOR_RESCHEDULE]);

            DB::commit();
            return $this->sendResponse('Booking approved successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function setBookingToReject(int $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->booking_status = Booking::STATUS_REJECTED;
            $booking->save();

            $booking->billing()->delete();

            DB::commit();
            return $this->sendResponse('Booking rejected successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function rescheduleBooking(int $bookingId, ReschedBookingRequest $request)
    {
        $validated = $request->validated();

        $existingBooking = Booking::where('id', '!=', $bookingId)
            ->where('booking_date', $validated['booking_date'])
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->first();

        if ($existingBooking) {
            return $this->sendError('Booking already exists for this date.', 400);
        }

        $booking = Booking::where('id', $bookingId)->first();

        if (!$booking) {
            return $this->sendError('Booking not found.', 404);
        }

        DB::beginTransaction();
        try {

            $booking->booking_date = $validated['booking_date'];
            $booking->ceremony_time = $validated['ceremony_time'];
            $booking->booking_status = Booking::STATUS_APPROVED;
            $booking->save();

            $this->bookingService->sendRescheduleMailToAdmin($booking);

            $this->bookingService->sendRescheduleMailToCustomer($booking);

            DB::commit();
            return $this->sendResponse('Booking rescheduled successfully.', new BookingResource($booking));
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->sendException($exception);
        }
    }

    public function getAvailablePackages()
    {
        $packages = Package::where('status', '=', Package::STATUS_ACTIVE)
            ->get(['id', 'package_name', 'package_details', 'package_price']);

        return $this->sendResponse('Packages retrieved successfully.', $packages);
    }

    public function getAvailableAddons(int $id)
    {
        $addons = Addon::where('status', '=', AddOn::STATUS_ACTIVE)->get();

        return $this->sendResponse('Addons retrieved successfully.', BookingAddOnsResource::collection($addons));
    }
}
