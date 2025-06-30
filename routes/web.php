<?php

use App\Mail\Client\CompletedBooking;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mailable/admin/booking', function () {
    $booking = App\Models\Booking::inRandomOrder()->first();

    return new App\Mail\Admin\ReceivedBooking($booking, 'John Doe');
});


Route::get('/mailable/client/booking', function () {
    $booking = App\Models\Booking::inRandomOrder()->first();

    return new App\Mail\Client\CompletedBooking($booking);
});

Route::get('/admin/reschedule', function () {
    $booking = App\Models\Booking::inRandomOrder()->first();

    return new App\Mail\Admin\ReceivedReschedule($booking, 'Lonie Jay');
});

Route::get('/client/reschedule', function () {
    $booking = App\Models\Booking::inRandomOrder()->first();

    return new App\Mail\Client\CompletedReschedule($booking);
});
