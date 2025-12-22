@php
    $userParticipation = $event->participations->where('id_user', Auth::id())->first();
    $myVote = $userParticipation ? $poll->votes->where('id_participation', $userParticipation->id_participation)->first() : null;
    $totalVotes = $poll->options->sum('votes_count');
    $isOrganizer = Auth::id() === $event->id_organizer;
    $isAdmin = Gate::allows('admin');
    $hasVoted = $myVote !== null;
    $canVote = $userParticipation && !$hasVoted;
    
    // Logic:
    // If I have voted -> Show Results (with Change Vote button)
    // If I am Organizer OR Admin -> Show Results
    // If I am Participant AND NOT voted -> Show Form
    // If I am NOT Participant -> Show Read-only Options
    
    $showResults = $hasVoted || $isOrganizer || $isAdmin;
    $showForm = $canVote && !$hasVoted; // Default state for participant
    
    // If organizer and not voted, we show results by default, but need a way to show form.
    // We will handle this with JS toggling.
@endphp

<div id="poll-card-{{ $poll->id_poll }}" class="bg-slate-50 rounded-xl p-4 border border-slate-200">
    <div class="flex justify-between items-start mb-3">
        <h3 class="font-semibold text-slate-900">{{ $poll->question }}</h3>
        @if($isOrganizer)
            <form action="{{ route('polls.destroy', $poll->id_poll) }}" method="POST" onsubmit="return confirm('Delete this poll?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-slate-400 hover:text-red-600 transition">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
    
    @if($showResults)
        {{-- Results View --}}
        <div id="poll-results-{{ $poll->id_poll }}" class="space-y-3">
            @foreach($poll->options as $option)
                @php
                    $percentage = $totalVotes > 0 ? ($option->votes_count / $totalVotes) * 100 : 0;
                    $isMyChoice = $myVote && $myVote->id_option === $option->id_option;
                @endphp
                <div class="relative">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium {{ $isMyChoice ? 'text-indigo-700' : 'text-slate-700' }}">
                            {{ $option->label }}
                            @if($isMyChoice) <i class="fas fa-check-circle ml-1 text-indigo-600"></i> @endif
                        </span>
                        <span class="text-slate-500 text-xs">{{ $option->votes_count }} votes ({{ round($percentage) }}%)</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
            @endforeach
            
            <div class="flex justify-between items-center mt-2">
                <p class="text-xs text-slate-400">Total votes: {{ $totalVotes }}</p>
                
                @if($event->effective_status !== 'canceled')
                    @if($hasVoted)
                        <form action="{{ route('polls.removeVote', $poll->id_poll) }}" method="POST" class="poll-remove-vote-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 underline">
                                Remove/Change Vote
                            </button>
                        </form>
                    @elseif($isOrganizer && $canVote)
                        <button onclick="document.getElementById('poll-results-{{ $poll->id_poll }}').classList.add('hidden'); document.getElementById('poll-form-{{ $poll->id_poll }}').classList.remove('hidden');" class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                            Cast Vote
                        </button>
                    @endif
                @endif
            </div>
        </div>
    @endif

    {{-- Voting Form --}}
    @if($canVote)
        <div id="poll-form-{{ $poll->id_poll }}" class="{{ $showResults ? 'hidden' : '' }}">
            @if($event->effective_status === 'canceled')
                <div class="space-y-2 opacity-60">
                    @foreach($poll->options as $option)
                        <div class="flex items-center justify-between text-sm text-slate-700 mb-1 p-2 border border-slate-200 rounded bg-white">
                            <span>{{ $option->label }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-center text-red-500 mt-2 italic">Voting disabled for canceled events.</p>
            @else
                <form action="{{ route('polls.vote', $poll->id_poll) }}" method="POST" class="poll-vote-form">
                    @csrf
                    <div class="space-y-2">
                        @foreach($poll->options as $option)
                            <label class="flex items-center p-3 rounded-lg border border-slate-200 bg-white hover:bg-indigo-50 hover:border-indigo-200 cursor-pointer transition group">
                                <input type="radio" name="option_id" value="{{ $option->id_option }}" class="h-4 w-4 text-indigo-600 border-slate-300 focus:ring-indigo-500" required>
                                <span class="ml-3 text-sm font-medium text-slate-700 group-hover:text-indigo-700">{{ $option->label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button type="submit" class="flex-1 inline-flex justify-center items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                            Submit Vote
                        </button>
                        @if($isOrganizer && $showResults)
                            <button type="button" onclick="document.getElementById('poll-form-{{ $poll->id_poll }}').classList.add('hidden'); document.getElementById('poll-results-{{ $poll->id_poll }}').classList.remove('hidden');" class="inline-flex justify-center items-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition">
                                Cancel
                            </button>
                        @endif
                    </div>
                </form>
            @endif
        </div>
    @endif

    {{-- Not a participant --}}
    @if(!$canVote && !$showResults)
        <div class="space-y-2 opacity-60">
            @foreach($poll->options as $option)
                <div class="flex items-center justify-between text-sm text-slate-700 mb-1 p-2 border border-slate-200 rounded bg-white">
                    <span>{{ $option->label }}</span>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-center text-slate-500 mt-2 italic">Join the event to vote and see results.</p>
    @endif
</div>