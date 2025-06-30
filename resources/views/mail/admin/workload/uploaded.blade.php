<x-mail::message>
# Hello {{ $recipient }},

This is to inform you that **{{ $employee_name }}** has uploaded new files to the following workload:

* Client Name: **{{ $client_name }}**
* Event Name: **{{ $event_name }}**
* Booking Date: **{{ $booking_date }}**
* Status: **{{ $workload_status }}**

<x-mail::button :url="$link['url']">
View Workload
</x-mail::button>

</x-mail::message>
