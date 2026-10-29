<meta name="csrf-token" content="<?= csrf_hash() ?>">
<script>
// Envia o token CSRF automaticamente em todo fetch() de mesma origem que não seja GET.
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta || !window.fetch) return;
    var token = meta.content, orig = window.fetch;
    window.fetch = function (input, init) {
        init = init || {};
        var method = (init.method || (input && input.method) || 'GET').toUpperCase();
        if (method !== 'GET' && method !== 'HEAD') {
            var h = new Headers(init.headers || (input && input.headers) || {});
            if (!h.has('X-CSRF-TOKEN')) h.set('X-CSRF-TOKEN', token);
            init.headers = h;
        }
        return orig.call(this, input, init);
    };
})();
</script>
