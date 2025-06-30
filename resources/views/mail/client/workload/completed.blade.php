<x-mail::message>
# Hello {{ $client_name }},

Thank you for choosing our service! We're happy to let to know
that your booking has been successfully completed.

* Event Name: **{{ $event_name }}**
* Completion Date: **{{ $completed_date }}**

If you have any feedback or questions, feel free to reach out.
We'd love to hear about your experience.

<x-mail::button :url="$drive_link">
View your images
</x-mail::button>

Best Regards,<br>
{{ config('app.name') }}
</x-mail::message>
