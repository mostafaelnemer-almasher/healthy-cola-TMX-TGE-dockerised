@component('mail::message')
@component('mail::panel')

{{-- Content  --}}
<center>
	<h2 class="center" style="font-size: 18px; color: #16a1fd; font-weight: 400; margin-bottom: 8px;">{!! $subject !!}</h2>
	<h1 class="start">{!! $greeting !!}</h1>
	<p class="start">{!! $message !!}</p>
</center>
@endcomponent

@endcomponent
