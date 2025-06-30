<x-mail::message>
# Hello {{ $receipient }},

Your booking has been successfully rescheduled. 
Please review the updated details below:

Booking Details:
* Event Name: **{{ $event_name }}**
* Package: **{{ $package_name }}**
* Add On/s: **{{ $add_ons }}**
* New Booking Date: **{{ $new_date }}**
* Ceremony Time: **{{ $ceremony_time }}**
* Status: **{{ $status }}**

<x-mail::button :url="''">
View your booking
</x-mail::button>

If you have any questions or need to make further changes, please don't hesitate to contact us.

Best Regards,<br>
{{ config('app.name') }}
</x-mail::message>
