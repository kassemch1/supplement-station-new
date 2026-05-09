<style>
    .rp-toast {
        position: fixed;
        bottom: 20px;
        left: 20px;
        z-index: 9998;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.18), 0 2px 6px rgba(0,0,0,0.08);
        padding: 12px 14px 12px 12px;
        display: none;
        align-items: center;
        gap: 12px;
        min-width: 280px;
        max-width: 360px;
        cursor: pointer;
        transform: translateY(20px);
        opacity: 0;
        transition: opacity .35s ease, transform .35s ease;
        font-family: inherit;
    }
    .rp-toast.rp-toast-visible { display: flex; opacity: 1; transform: translateY(0) !important; }
    .rp-toast:hover { box-shadow: 0 16px 40px rgba(0,0,0,0.22), 0 4px 8px rgba(0,0,0,0.1); }
    .rp-toast-img {
        flex: 0 0 56px;
        width: 56px; height: 56px;
        border-radius: 8px;
        background: #f1f5f9;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
    }
    .rp-toast-img img { width: 100%; height: 100%; object-fit: cover; }
    .rp-toast-body { flex: 1; min-width: 0; }
    .rp-toast-line1 { font-size: 13px; color: #334155; line-height: 1.3; }
    .rp-toast-line2 {
        font-size: 14px; font-weight: 600; color: #0f172a;
        line-height: 1.3; margin: 2px 0;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .rp-toast-line3 { font-size: 11px; color: #94a3b8; }
    .rp-toast-close {
        flex: 0 0 auto;
        width: 22px; height: 22px;
        background: transparent; border: none;
        color: #94a3b8; cursor: pointer;
        font-size: 16px; line-height: 1;
        padding: 0;
        align-self: flex-start;
    }
    .rp-toast-close:hover { color: #475569; }

    @media (max-width: 768px) {
        .rp-toast {
            left: 12px; right: 12px;
            top: 12px; bottom: auto;
            min-width: 0; max-width: none;
            transform: translateY(-20px);
        }
    }
</style>

<div id="rpToast" class="rp-toast" role="alert" aria-live="polite">
    <div class="rp-toast-img"><img id="rpToastImg" src="" alt=""></div>
    <div class="rp-toast-body">
        <div class="rp-toast-line1">Someone in <span id="rpToastCity">—</span></div>
        <div class="rp-toast-line2">Purchased <span id="rpToastProduct">—</span></div>
        <div class="rp-toast-line3" id="rpToastTime">just now</div>
    </div>
    <button type="button" class="rp-toast-close" id="rpToastClose" aria-label="Dismiss">&times;</button>
</div>

<script>
(function () {
    var endpoint = "{{ route('api.recentPurchases') }}";
    var toast = document.getElementById('rpToast');
    if (!toast) return;
    var imgEl = document.getElementById('rpToastImg');
    var cityEl = document.getElementById('rpToastCity');
    var nameEl = document.getElementById('rpToastProduct');
    var timeEl = document.getElementById('rpToastTime');
    var closeBtn = document.getElementById('rpToastClose');

    var purchases = [];
    var idx = 0;
    var currentUrl = '#';
    var hideTimer = null;
    var nextTimer = null;
    var dismissed = false;

    var SHOW_MS = 10000;
    var GAP_MS = 15000;
    var FIRST_DELAY_MS = 4000;

    function show(item) {
        if (!item) return;
        currentUrl = item.product_url || '#';
        cityEl.textContent = item.city ? item.city : 'Lebanon';
        nameEl.textContent = item.product_name || '';
        timeEl.textContent = item.time_ago || 'recently';
        if (item.image) {
            imgEl.src = item.image;
            imgEl.style.display = '';
        } else {
            imgEl.style.display = 'none';
        }
        toast.classList.add('rp-toast-visible');
    }

    function hide() {
        toast.classList.remove('rp-toast-visible');
    }

    function loop() {
        if (dismissed || !purchases.length) return;
        var item = purchases[idx % purchases.length];
        idx++;
        show(item);
        clearTimeout(hideTimer);
        clearTimeout(nextTimer);
        hideTimer = setTimeout(function () {
            hide();
            nextTimer = setTimeout(loop, GAP_MS);
        }, SHOW_MS);
    }

    toast.addEventListener('click', function (e) {
        if (e.target === closeBtn) return;
        if (currentUrl && currentUrl !== '#') {
            window.location.href = currentUrl;
        }
    });

    closeBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        dismissed = true;
        clearTimeout(hideTimer);
        clearTimeout(nextTimer);
        hide();
    });

    fetch(endpoint, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : []; })
        .then(function (data) {
            purchases = Array.isArray(data) ? data : [];
            if (!purchases.length) return;
            // shuffle once so order isn't always the same on reload
            for (var i = purchases.length - 1; i > 0; i--) {
                var j = Math.floor(Math.random() * (i + 1));
                var t = purchases[i]; purchases[i] = purchases[j]; purchases[j] = t;
            }
            setTimeout(loop, FIRST_DELAY_MS);
        })
        .catch(function () { /* silent */ });
})();
</script>
