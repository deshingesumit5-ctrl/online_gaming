@props([
    'balance' => 0,
    'showLabel' => true,
    'href' => null,
])

@php
    $formatted = number_format((int) $balance, 0);
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/90 border border-amber-500/40 hover:border-amber-400 transition shadow-inner whitespace-nowrap text-xs']) }} title="Wallet Balance">
    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
    <div class="flex items-center gap-1 leading-none font-bold">
        @if($showLabel)
            <span class="text-[9px] text-slate-400 uppercase tracking-wider font-extrabold hidden md:inline">POINTS:</span>
        @endif
        <span class="text-amber-300 font-black tracking-wide user-wallet-balance">{{ $formatted }}</span>
        <small class="text-[10px] text-amber-500 font-extrabold">PTS</small>
    </div>
</{{ $tag }}>
