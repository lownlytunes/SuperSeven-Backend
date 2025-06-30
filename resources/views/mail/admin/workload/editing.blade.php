<x-mail::message>
# Hello {{ $recipient }},

The status of the following Workload **{{ $event_name }}** has been updated to **{{ $workload_status }}**.

* Client Name: **{{ $client_name }}**
* Event Name: **{{ $event_name }}**
* Booking Date: **{{ $booking_date }}**
* Date: **{{ $date }}**
* Updated by: **{{ $employee_name }}**

<x-mail::button :url="$link['url']">
View Workload
</x-mail::button>
</x-mail::message>
