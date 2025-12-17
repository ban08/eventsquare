@extends('layouts.app')

@section('title', 'Admin – Reports')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gradient-to-b from-slate-50 to-slate-100 py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-slate-900">Manage Event Reports</h1>
        </div>

        {{-- Filters --}}
        <div class="mb-6 flex gap-2">
            <a href="{{ route('admin.reports.index', ['status' => 'open']) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium {{ $status === 'open' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                Open
            </a>
            <a href="{{ route('admin.reports.index', ['status' => 'closed']) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium {{ $status === 'closed' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                Dismissed/Resolved
            </a>
            <a href="{{ route('admin.reports.index', ['status' => 'all']) }}" 
               class="px-4 py-2 rounded-full text-sm font-medium {{ $status === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                All
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow border border-slate-200">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Event</th>
                        <th class="px-6 py-3">Reporter</th>
                        <th class="px-6 py-3">Reason</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($reports as $report)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium text-slate-900">#{{ $report->id_report }}</td>
                        <td class="px-6 py-4">
                            @if($report->event)
                                <a href="{{ route('events.show', $report->event->id_event) }}" class="text-indigo-600 hover:underline font-medium">
                                    {{ Str::limit($report->event->title, 30) }}
                                </a>
                            @else
                                <span class="text-slate-400 italic">Event Deleted</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($report->user)
                                <a href="{{ route('profile.show', $report->user->id_user) }}" class="text-slate-700 hover:text-indigo-600">
                                    {{ $report->user->name }}
                                </a>
                            @else
                                <span class="text-slate-400">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            <div class="max-w-xs">
                                <span class="block truncate" title="{{ $report->reason }}">
                                    {{ Str::limit($report->reason, 50) }}
                                </span>
                                @if(strlen($report->reason) > 50)
                                    <button onclick="openReportModal('{{ $report->id_report }}', '{{ e($report->reason) }}', '{{ $report->user ? e($report->user->name) : 'Unknown' }}', '{{ $report->event ? e($report->event->title) : 'Event Deleted' }}', '{{ $report->created_at->format('M d, Y H:i') }}')" 
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium mt-1 focus:outline-none">
                                        Read full reason
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $report->status === 'open' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs">
                            {{ $report->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            @if($report->status === 'open')
                                <form action="{{ route('admin.reports.update', $report->id_report) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="resolve">
                                    <button type="submit" class="text-green-600 hover:text-green-900 font-medium text-xs" onclick="return confirm('Mark as resolved?')">Resolve</button>
                                </form>
                                <form action="{{ route('admin.reports.update', $report->id_report) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="action" value="dismiss">
                                    <button type="submit" class="text-slate-500 hover:text-slate-700 font-medium text-xs" onclick="return confirm('Dismiss this report?')">Dismiss</button>
                                </form>
                            @else
                                <span class="text-slate-400 text-xs italic">Closed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-check-circle text-4xl text-slate-200 mb-3"></i>
                                <p>No reports found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    </div>
</div>

{{-- Report Details Modal --}}
<div id="report-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden backdrop-blur-sm" onclick="if(event.target === this) closeReportModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-semibold text-slate-900">Report Details <span id="modal-report-id" class="text-slate-400 text-sm font-normal ml-2"></span></h3>
            <button onclick="closeReportModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Event</label>
                <p id="modal-event" class="text-slate-900 font-medium"></p>
            </div>
            <div class="flex gap-6">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Reporter</label>
                    <p id="modal-reporter" class="text-slate-900"></p>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Date</label>
                    <p id="modal-date" class="text-slate-900"></p>
                </div>
            </div>
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Reason</label>
                <p id="modal-reason" class="text-slate-700 text-sm leading-relaxed whitespace-pre-wrap"></p>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 flex justify-end">
            <button onclick="closeReportModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 font-medium text-sm hover:bg-slate-50 rounded-lg transition-colors shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function openReportModal(id, reason, reporter, event, date) {
        document.getElementById('modal-report-id').textContent = '#' + id;
        document.getElementById('modal-reason').textContent = reason; // textContent prevents XSS
        document.getElementById('modal-reporter').textContent = reporter;
        document.getElementById('modal-event').textContent = event;
        document.getElementById('modal-date').textContent = date;
        
        document.getElementById('report-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeReportModal() {
        document.getElementById('report-modal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeReportModal();
        }
    });
</script>
@endsection
