@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="min-h-[calc(100vh-4rem)] bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
                <p class="text-sm text-slate-500 mt-1">Manage your event invitations and updates</p>
            </div>
            
            @if($unreadCount > 0)
                <button id="mark-all-read-btn" data-url="{{ route('notifications.markAllRead') }}" 
                        class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                    <i class="fas fa-check-double mr-2"></i>
                    Mark all as read
                </button>
            @endif
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="flex border-b border-slate-200">
                <button onclick="switchTab('inbox')" id="tab-btn-inbox" 
                        class="flex-1 py-4 text-sm font-medium text-center border-b-2 border-indigo-500 text-indigo-600 hover:bg-slate-50 transition-colors relative">
                    Inbox
                    @if($pendingInvitations->count() > 0)
                        <span id="inbox-badge" class="ml-2 bg-indigo-100 text-indigo-700 py-0.5 px-2 rounded-full text-xs">{{ $pendingInvitations->count() }}</span>
                    @endif
                </button>
                <button onclick="switchTab('activity')" id="tab-btn-activity"
                        class="flex-1 py-4 text-sm font-medium text-center border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors relative">
                    Activity
                </button>
            </div>

            {{-- Inbox Tab (Invitations) --}}
            <div id="tab-content-inbox" class="divide-y divide-slate-100">
                {{-- Pending Section --}}
                <div class="p-6">
                    <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Pending Invitations</h2>
                    
                    @if($pendingInvitations->isEmpty())
                        <div id="inbox-empty-state" class="text-center py-12">
                            <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-envelope-open text-slate-300 text-2xl"></i>
                            </div>
                            <h3 class="text-slate-900 font-medium">No pending invitations</h3>
                            <p class="text-slate-500 text-sm mt-1">You're all caught up!</p>
                        </div>
                    @else
                        <div id="invitation-list" class="space-y-4">
                            @foreach($pendingInvitations as $inv)
                                @php
                                    $effectiveStatus = $inv->event->effective_status ?? 'unknown';
                                    $isInvalid = in_array($effectiveStatus, ['canceled', 'completed', 'deleted']);
                                    $eventDate = $inv->event->start_at;
                                @endphp
                                <div class="invitation-item flex flex-col sm:flex-row gap-4 p-4 rounded-xl border {{ $isInvalid ? 'border-slate-200 bg-slate-50' : 'border-indigo-100 bg-white shadow-sm' }} transition-all hover:shadow-md">
                                    {{-- Date Box --}}
                                    <div class="hidden sm:flex flex-col items-center justify-center w-16 h-16 rounded-lg {{ $isInvalid ? 'bg-slate-200 text-slate-400' : 'bg-indigo-50 text-indigo-600' }} shrink-0">
                                        <span class="text-xs font-bold uppercase">{{ $eventDate ? $eventDate->format('M') : '??' }}</span>
                                        <span class="text-xl font-bold">{{ $eventDate ? $eventDate->format('d') : '??' }}</span>
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <h3 class="font-semibold text-slate-900 truncate">
                                                    <a href="{{ route('events.show', $inv->event->id_event) }}" class="hover:underline">
                                                        {{ $inv->event->title }}
                                                    </a>
                                                </h3>
                                                <p class="text-sm text-slate-500 mt-0.5">
                                                    by <span class="font-medium text-slate-700">{{ $inv->event->organizer->name ?? 'Unknown' }}</span>
                                                    @if($inv->event->venue)
                                                        &bull; {{ $inv->event->venue }}
                                                    @endif
                                                </p>
                                            </div>
                                            @if($isInvalid)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ ucfirst($effectiveStatus) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Invited
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="mt-4 flex items-center justify-between">
                                            <span class="text-xs text-slate-400">
                                                Sent {{ $inv->sent_at ? $inv->sent_at->diffForHumans() : 'recently' }}
                                            </span>
                                            
                                            <div class="flex gap-2">
                                                @if($isInvalid)
                                                    <span class="text-xs text-slate-500 italic py-2">Cannot join {{ $effectiveStatus }} event</span>
                                                @else
                                                    <button data-url="{{ route('invitations.decline', $inv->id_invitation) }}" 
                                                            class="ajax-action-btn px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                                        Decline
                                                    </button>
                                                    <button data-url="{{ route('invitations.accept', $inv->id_invitation) }}" 
                                                            class="ajax-action-btn px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-colors">
                                                        Accept Invitation
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- History Section --}}
                @if($respondedInvitations->isNotEmpty())
                    <div class="p-6 bg-slate-50/50">
                        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Past Invitations</h2>
                        <div class="space-y-3">
                            @foreach($respondedInvitations as $inv)
                                <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-2 h-2 rounded-full {{ $inv->status === 'accepted' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                        <span class="text-sm font-medium text-slate-700 truncate">{{ $inv->event->title }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs font-medium {{ $inv->status === 'accepted' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ucfirst($inv->status) }}
                                        </span>
                                        <span class="text-xs text-slate-400">{{ $inv->responded_at ? $inv->responded_at->format('M d') : '' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Activity Tab (Notifications) --}}
            <div id="tab-content-activity" class="hidden">
                @if($notifications->isEmpty())
                    <div id="activity-empty-state" class="text-center py-12">
                        <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-bell-slash text-slate-300 text-2xl"></i>
                        </div>
                        <h3 class="text-slate-900 font-medium">No notifications</h3>
                        <p class="text-slate-500 text-sm mt-1">We'll notify you when something happens.</p>
                    </div>
                @else
                    <ul id="notification-list" class="divide-y divide-slate-100">
                        @foreach($notifications as $notification)
                            <li class="notification-item group relative p-4 hover:bg-slate-50 transition-colors {{ !$notification->is_read ? 'bg-indigo-50/30' : '' }}">
                                <div class="flex gap-4">
                                    <div class="shrink-0 mt-1">
                                        @if($notification->message === 'invited')
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                                <i class="fas fa-envelope text-xs"></i>
                                            </div>
                                        @elseif($notification->message === 'event updated')
                                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                                <i class="fas fa-calendar-check text-xs"></i>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                                                <i class="fas fa-bell text-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900">
                                            {{ $notification->display_message }}
                                        </p>
                                        @if($notification->event)
                                            <a href="{{ route('events.show', $notification->event->id_event) }}" class="text-sm text-indigo-600 hover:underline block mt-0.5 truncate">
                                                {{ $notification->event->title }}
                                            </a>
                                        @endif
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    @if(!$notification->is_read)
                                        <div class="shrink-0 self-center">
                                            <button data-url="{{ route('notifications.markRead', $notification->id_notification) }}" 
                                                    class="ajax-action-btn w-2 h-2 rounded-full bg-indigo-600 hover:ring-4 hover:ring-indigo-100 transition-all" 
                                                    title="Mark as read"></button>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tab) {
        // Hide all contents
        document.getElementById('tab-content-inbox').classList.add('hidden');
        document.getElementById('tab-content-activity').classList.add('hidden');
        
        // Reset buttons
        const btnInbox = document.getElementById('tab-btn-inbox');
        const btnActivity = document.getElementById('tab-btn-activity');
        
        btnInbox.classList.remove('border-indigo-500', 'text-indigo-600');
        btnInbox.classList.add('border-transparent', 'text-slate-500');
        
        btnActivity.classList.remove('border-indigo-500', 'text-indigo-600');
        btnActivity.classList.add('border-transparent', 'text-slate-500');
        
        // Activate selected
        document.getElementById('tab-content-' + tab).classList.remove('hidden');
        const activeBtn = document.getElementById('tab-btn-' + tab);
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        activeBtn.classList.add('border-indigo-500', 'text-indigo-600');

        // Update URL without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize tab
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'inbox';
        switchTab(tab);

        // Helper to get CSRF token
        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

        // Handle individual AJAX actions (Mark Read, Accept, Decline)
        document.querySelectorAll('.ajax-action-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = btn.dataset.url;
                const method = 'POST';
                
                // Visual feedback immediately
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Success: Remove the item from DOM
                        const item = btn.closest('.invitation-item') || btn.closest('.notification-item');
                        
                        // Decrement badge count if it's an invitation
                        if (btn.closest('.invitation-item')) {
                            const badge = document.getElementById('inbox-badge');
                            if (badge) {
                                let count = parseInt(badge.innerText);
                                if (count > 1) {
                                    badge.innerText = count - 1;
                                } else {
                                    badge.remove();
                                }
                            }
                        }

                        if (item) {
                            item.style.transition = 'all 0.3s ease';
                            item.style.opacity = '0';
                            item.style.transform = 'translateX(10px)';
                            setTimeout(() => {
                                item.remove();
                                checkEmptyStates();
                            }, 300);
                        }
                    } else {
                        alert(data.message || 'An error occurred.');
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred.');
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            });
        });

        // Handle Mark All Read
        const markAllBtn = document.getElementById('mark-all-read-btn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = markAllBtn.dataset.url;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    if (response.ok) {
                        // Remove all unread indicators visually
                        document.querySelectorAll('.notification-item').forEach(item => {
                            item.classList.remove('bg-indigo-50/30');
                            const dot = item.querySelector('.ajax-action-btn'); // The read dot
                            if (dot) dot.remove();
                        });
                        markAllBtn.remove(); // Remove the button itself
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        }

        // Helper to check if lists are empty and show empty states
        function checkEmptyStates() {
            // Check Invitations
            const invList = document.getElementById('invitation-list');
            if (invList && invList.children.length === 0) {
                invList.remove();
                const badge = document.getElementById('inbox-badge');
                if (badge) badge.remove();
                
                // Show empty state
                const container = document.getElementById('tab-content-inbox').querySelector('.p-6');
                if (container) {
                    container.innerHTML = `
                        <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Pending Invitations</h2>
                        <div id="inbox-empty-state" class="text-center py-12">
                            <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-envelope-open text-slate-300 text-2xl"></i>
                            </div>
                            <h3 class="text-slate-900 font-medium">No pending invitations</h3>
                            <p class="text-slate-500 text-sm mt-1">You're all caught up!</p>
                        </div>
                    `;
                }
            }

            // Check Notifications
            const notifList = document.getElementById('notification-list');
            if (notifList && notifList.children.length === 0) {
                notifList.remove();
                const container = document.getElementById('tab-content-activity');
                if (container) {
                    container.innerHTML = `
                        <div id="activity-empty-state" class="text-center py-12">
                            <div class="bg-slate-50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-bell-slash text-slate-300 text-2xl"></i>
                            </div>
                            <h3 class="text-slate-900 font-medium">No notifications</h3>
                            <p class="text-slate-500 text-sm mt-1">We'll notify you when something happens.</p>
                        </div>
                    `;
                }
            }
        }
    });
</script>
@endpush
@endsection
