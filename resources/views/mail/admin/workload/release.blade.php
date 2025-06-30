<x-mail::message>
# Hi {{ $recipient }},

The booking for **{{ $event_name }}** has moved to the **Releasing** stage.

* Client Name: **{{ $client_name }}**
* Event Name: **{{ $event_name }}**
* Booking Date: **{{ $booking_date }}**
* Date: **{{ $date }}**
* Released by: **{{ $employee_name }}**

<x-mail::button :url="$link['url']">
View Workload
</x-mail::button>

Please review all final outputs carefully before releasing to the client.
</x-mail::message>
