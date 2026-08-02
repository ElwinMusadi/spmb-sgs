@props([
    'question' => '',
    'answer' => '',
])

<div x-data="{ open: false }" {{ $attributes->merge(['class' => 'border border-outline-variant/20 rounded-xl overflow-hidden transition-all duration-300 hover:border-royal/30']) }}>
    <button
        @click="open = !open"
        class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left bg-white hover:bg-surface-container/50 transition-colors duration-200 cursor-pointer"
        :aria-expanded="open"
    >
        <span class="font-heading text-base font-semibold text-navy-dark">{{ $question }}</span>
        <span class="shrink-0 w-8 h-8 rounded-full bg-royal-pale/50 flex items-center justify-center transition-all duration-300" :class="{ 'rotate-180 bg-royal-pale': open }">
            <svg class="w-4 h-4 text-royal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
    >
        <div class="px-6 pb-5 pt-0">
            <p class="font-body text-sm text-on-surface-variant leading-relaxed">{{ $answer }}</p>
        </div>
    </div>
</div>
