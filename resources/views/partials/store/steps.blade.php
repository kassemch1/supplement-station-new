{{-- Cart → Details → Done, on the cart and checkout pages --}}
<ol class="ss-steps" aria-label="Order steps">
    @foreach(['Cart', 'Details', 'Done'] as $index => $label)
        <li class="{{ $index + 1 < $step ? 'is-done' : ($index + 1 === $step ? 'is-current' : '') }}" @if($index + 1 === $step) aria-current="step" @endif>
            <span class="ss-steps__dot">{{ $index + 1 }}</span>{{ $label }}
        </li>
    @endforeach
</ol>
