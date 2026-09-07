@props([
    'title',
    'value',
    'icon' => '📊',
    'accent' => 'amber', // 'amber', 'emerald', 'blue', 'red', 'purple'
    'subtext' => null,
    'badge' => null,
    'badgeColor' => 'emerald',
])

@php
    $borders = [
        'amber' => 'hover:border-amber-500/50 border-slate-800/80',
        'emerald' => 'hover:border-emerald-500/50 border-slate-800/80',
        'red' => 'hover:border-red-500/50 border-slate-800/80',
        'blue' => 'hover:border-blue-500/50 border-slate-800/80',
        'purple' => 'hover:border-purple-500/50 border-slate-800/80',
    ];
    $iconBgs = [
        'amber' => 'bg-amber-950/80 border-amber-500/40 text-amber-400 shadow-[0_0_10px_rgba(245,158,11,0.2)]',
        'emerald' => 'bg-emerald-950/80 border-emerald-500/40 text-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.2)]',
        'red' => 'bg-red-950/80 border-red-500/40 text-red-400 shadow-[0_0_10px_rgba(239,68,68,0.2)]',
        'blue' => 'bg-blue-950/80 border-blue-500/40 text-blue-400 shadow-[0_0_10px_rgba(59,130,246,0.2)]',
        'purple' => 'bg-purple-950/80 border-purple-500/40 text-purple-400 shadow-[0_0_10px_rgba(168,85,247,0.2)]',
    ];
@endphp

<div class="rounded-2xl bg-[#0c1322]/85 backdrop-blur border {{ $borders[$accent] ?? 'border-slate-800' }} p-5 shadow-lg relative overflow-hidden transition group">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-slate-400 tracking-wide uppercase">{{ $title }}</span>
        <span class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm {{ $iconBgs[$accent] ?? 'bg-slate-800' }}">
            {{ $icon }}
        </span>
    </div>
    <div class="flex items-baseline justify-between">
        <span class="text-2xl lg:text-3xl font-black font-royal text-white tracking-wide">
            {{ $value }}
        </span>
        @if($badge)
            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md border
                {{ $badgeColor === 'emerald' ? 'text-emerald-400 bg-emerald-950/60 border-emerald-500/30' : '' }}
                {{ $badgeColor === 'red' ? 'text-red-400 bg-red-950/60 border-red-500/30' : '' }}
                {{ $badgeColor === 'amber' ? 'text-amber-400 bg-amber-950/60 border-amber-500/30' : '' }}">
                {{ $badge }}
            </span>
        @endif
    </div>
    @if($subtext)
        <p class="text-[11px] text-slate-400 mt-2">{{ $subtext }}</p>
    @endif
</div>
