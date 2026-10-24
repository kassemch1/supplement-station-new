{{-- "Someone in ... purchased" toast; styles in resources/assets/css/store.css, script in resources/assets/js/store.js --}}

<div id="rpToast" class="rp-toast" role="alert" aria-live="polite">
    <div class="rp-toast-img"><img id="rpToastImg" alt=""></div>
    <div class="rp-toast-body">
        <div class="rp-toast-line1">Someone in <span id="rpToastCity">—</span></div>
        <div class="rp-toast-line2">Purchased <span id="rpToastProduct">—</span></div>
        <div class="rp-toast-line3" id="rpToastTime">just now</div>
    </div>
    <button type="button" class="rp-toast-close" id="rpToastClose" aria-label="Dismiss">&times;</button>
</div>

