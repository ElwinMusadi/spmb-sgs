@props([
    'icon' => 'star',
    'title' => '',
    'description' => '',
])

<div
  {{ $attributes->merge(['class' => 'group relative bg-white rounded-2xl p-8 shadow-[0_2px_12px_rgba(36,48,112,0.05)] hover:shadow-[0_8px_24px_rgba(36,48,112,0.08)] transition-all duration-300 border border-outline-variant/20']) }}>
  {{-- Icon Container --}}
  <div
    class="w-14 h-14 rounded-xl bg-royal-pale/50 flex items-center justify-center mb-5 group-hover:bg-royal-pale transition-colors duration-300">
    {{ $slot }}
  </div>

  <h3 class="font-heading text-lg font-semibold text-navy-dark mb-2">{{ $title }}</h3>
  <p class="font-body text-sm text-on-surface-variant leading-relaxed">{{ $description }}</p>
</div>
