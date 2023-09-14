@component('mail::message')
    Hello, {{$creatorName}}! <br>


    Your card @if($cardTitleBeforeUpdate){{$cardTitleBeforeUpdate}} (new title {{$cardTitle}})@else{{$cardTitle}}@endif was replaced to {{$columnTitle}} by team member {{$userEmail}}.
@endcomponent
