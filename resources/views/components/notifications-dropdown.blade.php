@auth
@php
    $notificationService = app(\App\Services\NotificationService::class);
    $currentUser = Auth::user();
    $unreadCount = $notificationService->getUnreadCount($currentUser);
    $notifications = $notificationService->getRecentNotifications($currentUser, 20);
@endphp

<div class="relative" id="notifications-dropdown-root">
    <!-- Bell Icon Button -->
    <button 
        id="notif-bell-btn" 
        type="button" 
        class="relative p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-200 hover:text-amber-400 transition border border-slate-700/60 flex items-center justify-center cursor-pointer shrink-0 focus:outline-none focus:ring-2 focus:ring-amber-500/40" 
        title="Notifications"
        aria-label="Notifications"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if($unreadCount > 0)
        <span 
            id="notif-badge" 
            class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full ring-2 ring-[#0d1322] leading-none min-w-[18px] text-center shadow-lg animate-pulse"
        >
            {{ $unreadCount }}
        </span>
        @endif
    </button>

    <!-- Mobile Backdrop -->
    <div id="notif-mobile-backdrop" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 sm:hidden"></div>

    <!-- Dropdown Panel -->
    <div 
        id="notif-dropdown-menu" 
        class="hidden bg-[#0d1322] border border-slate-700/90 rounded-2xl shadow-2xl overflow-hidden transition-all duration-200 ease-out backdrop-blur-md flex flex-col"
        role="menu"
    >
    <style>
        @media (max-width: 639px) {
            #notif-dropdown-menu {
                position: fixed !important;
                left: 50% !important;
                top: 60px !important;
                right: auto !important;
                bottom: auto !important;
                transform: translateX(-50%) !important;
                width: calc(100vw - 24px) !important;
                max-width: 420px !important;
                max-height: 82vh !important;
                margin: 0 !important;
                z-index: 99999 !important;
            }
            #notif-mobile-backdrop {
                z-index: 99990 !important;
            }
        }
        @media (min-width: 640px) {
            #notif-dropdown-menu {
                position: absolute !important;
                right: 0 !important;
                left: auto !important;
                top: 100% !important;
                bottom: auto !important;
                transform: none !important;
                width: 24rem !important;
                max-height: none !important;
                margin-top: 0.5rem !important;
                z-index: 50 !important;
            }
        }
    </style>
        <!-- Dropdown Header -->
        <div class="px-4 py-3 bg-[#090d16] border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-amber-300 font-royal flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                </span>
                <span id="notif-header-count" class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $unreadCount > 0 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-slate-800 text-slate-400' }}">
                    {{ $unreadCount > 0 ? $unreadCount . ' new' : 'All caught up' }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                @if($notifications->count() > 0)
                <button 
                    id="notif-mark-all-btn" 
                    type="button" 
                    class="text-[11px] text-slate-400 hover:text-amber-400 transition cursor-pointer font-medium hover:underline"
                >
                    Mark all as read
                </button>
                @endif
                <button 
                    id="notif-mobile-close-btn"
                    type="button"
                    class="sm:hidden p-1 rounded-lg text-slate-400 hover:text-white bg-slate-800/80 text-xs font-bold leading-none"
                    aria-label="Close"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-[60vh] sm:max-h-[380px] overflow-y-auto divide-y divide-slate-800/80 custom-scrollbar flex-1" id="notif-list-container">
            @forelse($notifications as $notif)
            <a 
                href="{{ $notif->link ?: '#' }}" 
                class="notif-item flex items-start gap-3 p-3.5 hover:bg-slate-800/60 transition-colors text-decoration-none group relative {{ !$notif->isRead() ? 'bg-slate-900/50' : '' }}"
                data-id="{{ $notif->id }}"
                data-link="{{ $notif->link ?: '#' }}"
            >
                <!-- Icon Circle -->
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-base shadow-sm {{ !$notif->isRead() ? 'bg-amber-500/20 border border-amber-500/40 text-amber-300' : 'bg-slate-800 border border-slate-700/60 text-slate-300' }}">
                    <span>{{ $notif->icon ?: '🔔' }}</span>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pr-2">
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <h4 class="text-xs font-semibold text-slate-100 group-hover:text-amber-300 transition truncate">
                            {{ $notif->title }}
                        </h4>
                        <span class="text-[10px] text-slate-500 shrink-0">
                            {{ $notif->created_at ? $notif->created_at->diffForHumans(null, true, true) : 'just now' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-300 line-clamp-3 leading-relaxed font-normal">
                        {{ $notif->message }}
                    </p>
                </div>

                <!-- Unread Indicator Dot -->
                @if(!$notif->isRead())
                <span class="notif-unread-dot w-2 h-2 rounded-full bg-amber-400 shrink-0 mt-2 ring-4 ring-amber-400/20"></span>
                @endif
            </a>
            @empty
            <div class="py-10 px-4 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center mx-auto mb-3 text-xl text-slate-400">
                    🔔
                </div>
                <p class="text-xs font-semibold text-slate-300 mb-1">No notifications yet</p>
                <p class="text-[11px] text-slate-500">We'll alert you when there is activity on your account.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('notifications-dropdown-root');
    if (!root) return;

    const bellBtn = document.getElementById('notif-bell-btn');
    const dropdown = document.getElementById('notif-dropdown-menu');
    const mobileBackdrop = document.getElementById('notif-mobile-backdrop');
    const mobileCloseBtn = document.getElementById('notif-mobile-close-btn');
    const badge = document.getElementById('notif-badge');
    const headerCount = document.getElementById('notif-header-count');
    const markAllBtn = document.getElementById('notif-mark-all-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function toggleDropdown() {
        const isHidden = dropdown.classList.contains('hidden');
        if (isHidden) {
            dropdown.classList.remove('hidden');
            if (mobileBackdrop) mobileBackdrop.classList.remove('hidden');
            bellBtn.setAttribute('aria-expanded', 'true');
            // When opened, clear the popup badge immediately as requested
            clearBadgeAndMarkRead();
        } else {
            closeDropdown();
        }
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        if (mobileBackdrop) mobileBackdrop.classList.add('hidden');
        bellBtn.setAttribute('aria-expanded', 'false');
    }

    window.closeNotificationsDropdown = closeDropdown;

    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeDropdown();
        });
    }

    if (mobileBackdrop) {
        mobileBackdrop.addEventListener('click', function(e) {
            e.stopPropagation();
            closeDropdown();
        });
    }

    function clearBadgeAndMarkRead() {
        if (badge && badge.style.display !== 'none') {
            badge.style.display = 'none';
            if (headerCount) {
                headerCount.textContent = 'All caught up';
                headerCount.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-400';
            }
            // Remove unread dots
            document.querySelectorAll('.notif-unread-dot').forEach(dot => dot.remove());

            // Send async background request to mark all read
            if (csrfToken) {
                fetch('{{ route("notifications.markAllRead") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                }).catch(err => console.error('Error marking notifications as read:', err));
            }
        }
    }

    bellBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleDropdown();
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            clearBadgeAndMarkRead();
        });
    }

    // Handle click on notification items
    document.querySelectorAll('.notif-item').forEach(item => {
        item.addEventListener('click', function (e) {
            const notifId = this.dataset.id;
            let targetLink = this.dataset.link || this.getAttribute('href');

            if (targetLink && (targetLink.startsWith('http://localhost') || targetLink.startsWith('http://127.0.0.1'))) {
                try {
                    const parsed = new URL(targetLink);
                    targetLink = parsed.pathname + parsed.search + parsed.hash;
                } catch (err) {}
            }

            // Mark this individual notification as read if unread
            if (notifId && csrfToken) {
                fetch(`/notifications/${notifId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    keepalive: true
                }).catch(err => console.error(err));
            }

            // Let browser navigate to target link
            if (targetLink && targetLink !== '#' && targetLink !== '') {
                e.preventDefault();
                window.location.href = targetLink;
            }
        });
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) {
            closeDropdown();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
});
</script>
@endauth
