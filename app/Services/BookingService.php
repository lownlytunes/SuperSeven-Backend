<?php

namespace App\Services;

use App\Mail\Admin\CancelledBooking;
use App\Mail\Admin\ReceivedBooking;
use App\Mail\Admin\ReceivedReschedule;
use App\Mail\Client\CancelledBooking as ClientCancelledBooking;
use App\Mail\Client\CompletedBooking;
use App\Mail\Client\CompletedReschedule;
use App\Models\AddOn;
use App\Models\Billing;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingService
{
    public function getFilterBookingData()
    {
        return [
            'booked' => [
                'type' => 'or',
                'condition' => "booking_status = " . Booking::STATUS_APPROVED,
            ],
            'pending' => [
                'type' => 'or',
                'condition' => "booking_status = " . Booking::STATUS_PENDING,
            ],
            'for_resched' => [
                'type' => 'or',
                'condition' => "booking_status = " . Booking::STATUS_FOR_RESCHEDULE,
            ],
        ];
    }

    public function createWalkinCustomer(string $firstName, string $lastName, string $address, string $email, string $contactNo)
    {
        $first_name = ucfirst(str_replace(' ', '', trim($firstName)));
        $last_name = strtolower(str_replace(' ', '', trim($lastName)));
        $rawPassword = $first_name . $last_name . '12345';

        $hashedPassword = Hash::make($rawPassword);

        $user = new User();
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->address = $address;
        $user->email = $email;
        $user->password = $hashedPassword;
        $user->contact_num = $contactNo;
        $user->save();

        Customer::create([
            'user_id' => $user->id,
            'customer_type' => User::CLIENT_TYPE,
        ]);

        return $user;
    }

    public function createBillingStatement(int $bookingId, int $packageId, array $addonIds, ?float $discount = null)
    {
        $package = Package::findOrFail($packageId);
        $addOnAmount = AddOn::whereIn('id', $addonIds)->sum('add_on_price');

        $baseTotal = $package->package_price + $addOnAmount;

        // Apply discount if any
        $discountAmount = ($discount > 0) ? ($baseTotal * ($discount / 100)) : 0;
        $totalAmount = $baseTotal - $discountAmount;

        Billing::create([
            'booking_id' => $bookingId,
            'package_amount' => $package->package_price,
            'add_on_amount' => $addOnAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    public function updateBillingStatement(
        int $bookingId,
        int $packageId,
        array $addOnIds,
        ?float $discount = null
    ) {
        $package = Package::findOrFail($packageId);
        $addOnAmount = AddOn::whereIn('id', $addOnIds)->sum('add_on_price');
        $baseTotal = $package->package_price + $addOnAmount;
        $discountAmount = $discount ? ($baseTotal * ($discount / 100)) : 0;

        Billing::updateOrCreate(
            ['booking_id' => $bookingId],
            [
                'package_amount' => $package->package_price,
                'add_on_amount' => $addOnAmount,
                'total_amount' => $baseTotal - $discountAmount,
            ]
        );
    }

    public function getDiscountPercentage(string $bookingDate)
    {
        $date = Carbon::parse($bookingDate);

        if ($date->greaterThanOrEqualTo(Carbon::now()->addYear())) {
            return 10;
        }

        return 0;
    }

    public function getUpcomingEvents()
    {
        $startOfWeek = Carbon::today();
        $endOfWeek = Carbon::today()->addDays(6)->endOfDay();

        return Booking::with('package')
            ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
            ->where('booking_status', Booking::STATUS_APPROVED)
            ->orderBy('booking_date')
            ->get()
            ->map(function ($booking) {
                return [
                    'event_name' => $booking->event_name,
                    'booking_date' => Carbon::parse($booking->booking_date)->format('F d, Y'),
                    'package' => $booking->package->package_name ?? '',
                    'booking_address' => $booking->booking_address
                ];
            });
    }

    public function sendReceivedMailToAdmin(Booking $booking)
    {
        try {
            $recipients = User::has('employee')
                ->whereHas('employee', function ($query) {
                    $query->whereIn('employee_type', [
                        User::OWNER_TYPE,
                        User::SECRETARY_TYPE
                    ]);
                })->get();
    
            if ($recipients->isEmpty()) {
                return false;
            }
    
            foreach ($recipients as $recipient) {
                $toSend = new ReceivedBooking($booking, $recipient->first_name);
                Mail::to($recipient->email)->queue($toSend);
            }
    
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send received booking email to admins.', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
    
            return false;
        }
    }

    public function sendSuccessMailToCustomer(Booking $booking)
    {
        try {
            if (!$booking || !$booking->customer || !$booking->customer->email) {
                return false;
            }
    
            $toSend = new CompletedBooking($booking);
            Mail::to($booking->customer->email)->queue($toSend);
    
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send completed booking email to customer.', [
                'booking_id' => $booking->id ?? null,
                'error' => $e->getMessage(),
            ]);
    
            return false;
        }
    }

    public function sendRescheduleMailToAdmin(Booking $booking)
    {
        $recipients = User::has('employee')
            ->whereHas('employee', function ($query) {
                $query->where('employee_type', User::OWNER_TYPE);
            })->get();
        
        if (!$recipients) {
            return false;
        }


        foreach ($recipients as $recipient) {
            $toSend = new ReceivedReschedule($booking, $recipient->first_name);

            Mail::to($recipient->email)->queue($toSend);
        }
    }

    public function sendRescheduleMailToCustomer(Booking $booking)
    {
        if (!$booking) {
            return false;
        }

        $toSend = new CompletedReschedule($booking);

        Mail::to($booking->customer->email)->queue($toSend);
    }

    public function sendCancellationMail(Booking $booking): bool
    {
        $adminSent = $this->sendCancelMailToAdmin($booking);
        $customerSent = $this->sendCancelMailToCustomer($booking);
        
        return $adminSent && $customerSent;
    }

    private function sendCancelMailToAdmin(Booking $booking)
    {
        $recipients = User::has('employee')
            ->whereHas('employee', function ($query) {
                $query->where('employee_type', User::OWNER_TYPE);
            })->get();
        
        if (!$recipients) {
            return false;
        }

        foreach ($recipients as $recipient) {
            $toSend = new CancelledBooking($booking, $recipient->first_name);

            Mail::to($recipient->email)->queue($toSend);
        }
    }

    private function sendCancelMailToCustomer(Booking $booking)
    {
        if (!$booking) {
            return false;
        }

        $toSend = new ClientCancelledBooking($booking);

        Mail::to($booking->customer->email)->queue($toSend);
    }
}
