@props(['status'])

@php
    $config = match (strtolower($status)) {
        'approved', 'processed', 'settled' => [
            'label' => 'APPROVED',
            'classes' => 'bg-emerald-950/80 text-emerald-300 border-emerald-500/60 shadow-[0_0_8px_rgba(16,185,129,0.15)]',
            'dot' => 'bg-emerald-400',
        ],
        'active' => [
            'label' => 'ACTIVE',
            'classes' => 'bg-emerald-950/80 text-emerald-300 border-emerald-500/50',
            'dot' => 'bg-emerald-400',
        ],
        'pending' => [
            'label' => 'PENDING',
            'classes' => 'bg-amber-950/80 text-amber-300 border-amber-600/50 animate-pulse',
            'dot' => 'bg-amber-400',
        ],
        'rejected' => [
            'label' => 'REJECTED',
            'classes' => 'bg-red-950/80 text-red-300 border-red-600/50',
            'dot' => 'bg-red-400',
        ],
        'blocked' => [
            'label' => 'BLOCKED',
            'classes' => 'bg-red-950/90 text-red-400 border-red-700/60',
            'dot' => 'bg-red-500',
        ],
        'inactive' => [
            'label' => 'INACTIVE',
            'classes' => 'bg-slate-800 text-slate-400 border-slate-700',
            'dot' => 'bg-slate-500',
        ],
        default => [
            'label' => strtoupper($status),
            'classes' => 'bg-slate-800 text-slate-300 border-slate-700',
            'dot' => 'bg-slate-400',
        ],
    };
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider border {{ $config['classes'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}"></span>
    {{ $config['label'] }}
</span>
