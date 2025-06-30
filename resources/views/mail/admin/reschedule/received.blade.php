<x-mail::message>
# Hello {{ $receipient }},

The booking for **{{ $event_name }}** has been rescheduled.

Updated Booking Details:
* Client Name: **{{  $client_name }}**
* Event Name: **{{ $event_name }}**
* Package: **{{ $package_name }}**
* Add On/s: **{{ $add_ons }}**
* New Booking Date: **{{ $new_date }}**
* Ceremony Time: **{{ $ceremony_time }}**

<x-mail::button :url="''">
View Booking Details
</x-mail::button>

Please check the details and inform any involved staff if necessary.
</x-mail::message>