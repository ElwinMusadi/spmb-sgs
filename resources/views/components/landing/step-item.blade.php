@props([
    'number' => '1',
    'title' => '',
    'description' => '',
    'isLast' => false,
])

<div {{ $attributes->merge(['class' => 'flex items-start gap-4 relative']) }}>
    {{-- Step Number Circle --}}
    <div class="relative z-10 flex flex-col items-center">
        <div class="w-12 h-12 rounded-full bg-navy text-white flex items-center justify-center font-heading text-lg font-bold shadow-md shrink-0">
            {{ $number }}
        </div>
        @unless($isLast)
            <div class="w-0.5 bg-gradient-to-b from-navy to-royal-dim/30 flex-1 min-h-[40px] mt-2"></div>
        @endunless
    </div>

    {{-- Step Content --}}
    <div class="pb-8 pt-1.5">
        <h4 class="font-heading text-base font-semibold text-navy-dark mb-1">{{ $title }}</h4>
        <p class="font-body text-sm text-on-surface-variant leading-relaxed">{{ $description }}</p>
    </div>
</div>
