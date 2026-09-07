@props(['type'])

@php
    $typeConfig = match ($type) {
        'manual_credit' => [
            'label' => 'MANUAL CREDIT',
            'classes' => 'bg-emerald-950/90 text-emerald-300 border-emerald-500/60 shadow-[0_0_8px_rgba(16,185,129,0.15)]',
        ],
        'points_added' => [
            'label' => 'POINTS ADDED',
            'classes' => 'bg-emerald-950/90 text-emerald-300 border-emerald-500/60 shadow-[0_0_8px_rgba(16,185,129,0.15)]',
        ],
        'winning_points_added' => [
            'label' => 'WINNING POINTS',
            'classes' => 'bg-emerald-950/90 text-emerald-300 border-emerald-400/60 shadow-[0_0_8px_rgba(16,185,129,0.2)]',
        ],
        'bet_cancelled_refunded', 'bet_refunded' => [
            'label' => 'REFUNDED',
            'classes' => 'bg-indigo-950/90 text-indigo-300 border-indigo-600/50',
        ],
        'bet_deducted' => [
            'label' => 'BET DEDUCTED',
            'classes' => 'bg-amber-950/80 text-amber-300 border-amber-600/50',
        ],
        'withdrawal' => [
            'label' => 'WITHDRAWAL',
            'classes' => 'bg-red-950/90 text-red-300 border-red-600/50',
        ],
        'manual_debit' => [
            'label' => 'MANUAL DEBIT',
            'classes' => 'bg-red-950/90 text-red-300 border-red-600/50',
        ],
        'adjustment' => [
            'label' => 'ADJUSTMENT',
            'classes' => 'bg-slate-800 text-slate-300 border-slate-600/60',
        ],
        default => [
            'label' => strtoupper(str_replace('_', ' ', $type)),
            'classes' => 'bg-slate-800 text-slate-300 border-slate-700',
        ],
    };
@endphp

<span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider border {{ $typeConfig['classes'] }}">
    {{ $typeConfig['label'] }}
</span>
