<details {{ $attributes->merge(['class' => 'accordion-item']) }}>
    <summary class="accordion-summary">{{ $title ?? '' }}</summary>
    <div class="accordion-body">
        {{ $slot }}
    </div>
</details>
