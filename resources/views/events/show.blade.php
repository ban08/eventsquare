@extends('layouts.app')

@section('title', $event->title)

@section('content')
    <article class="event-detail">
        <h2>{{ $event->title }}</h2>

        <p><strong>Date:</strong> {{ $event->start_at }}</p>
        <p><strong>Venue:</strong> {{ $event->venue }}</p>
        <p><strong>Description:</strong></p>
        <p>{{ $event->description }}</p>
    </article>

    <p><a href="{{ route('events.index') }}">Back to events</a></p>
@endsection
