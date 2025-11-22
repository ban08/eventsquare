@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h2>Upcoming Events</h2>

    @if ($events->isEmpty())
        <p>No upcoming public events.</p>
    @else
        <ul class="event-list">
            @foreach ($events as $event)
                <li>
                    <a href="{{ route('events.show', $event->id_event) }}">{{ $event->title }}</a>
                    <span>{{ $event->start_at }}</span>
                    <span>{{ $event->venue }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <p><a href="{{ route('events.index') }}">Browse all events</a></p>
@endsection
