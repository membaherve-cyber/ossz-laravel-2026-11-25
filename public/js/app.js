/* OSSZ Collections — storefront JS (nav, concierge chat, product cards, PWA). */
(function () {
    'use strict';

    // ── PWA service worker (relative scope, same-origin caches only) ──
    // The query string version is deliberate: it makes the script URL
    // unique per release, so browsers install the NEW worker even if an
    // intermediate cache is still serving the old /sw.js bytes.
    if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
        navigator.serviceWorker.register('/sw.js?v=3', { scope: '/' }).catch(function () { /* offline support is best-effort */ });
    }

    // ── Mobile nav ────────────────────────────────────────────────────
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    // ── Concierge panel ───────────────────────────────────────────────
    var btn = document.querySelector('.concierge-btn');
    var panel = document.querySelector('.concierge-panel');
    var log = document.querySelector('.concierge-log');
    var form = document.querySelector('.concierge-form');
    var input = form ? form.querySelector('input') : null;
    var history = [];
    var sending = false;

    function isFr() {
        return document.documentElement.lang.indexOf('fr') === 0;
    }

    function push(role, content) {
        history.push({ role: role, content: content });
        if (history.length > 10) history.shift();
    }

    function bubble(role, content) {
        var div = document.createElement('div');
        div.className = 'msg msg-' + (role === 'user' ? 'user' : 'bot');
        div.textContent = content;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
        return div;
    }

    function renderCards(reply) {
        // "intro CARD_START: PRODUCT:slug|NAME:x|PRICE:y|STOCK:z|IMG:u ... :CARD_END"
        var match = reply.match(/CARD_START:([\s\S]*?):CARD_END/);
        if (!match) {
            bubble('bot', reply);
            return;
        }
        var intro = reply.slice(0, reply.indexOf('CARD_START:')).trim();
        if (intro) bubble('bot', intro);

        var wrap = document.createElement('div');
        wrap.className = 'msg msg-bot';
        match[1].trim().split('\n').forEach(function (line) {
            var f = {};
            line.split('|').forEach(function (pair) {
                var i = pair.indexOf(':');
                if (i > 0) f[pair.slice(0, i)] = pair.slice(i + 1);
            });
            if (!f.PRODUCT) return;
            var a = document.createElement('a');
            a.href = '/product/' + f.PRODUCT;
            if (f.IMG) {
                var img = document.createElement('img');
                img.src = f.IMG;
                img.alt = f.NAME || '';
                img.loading = 'lazy';
                a.appendChild(img);
            }
            var span = document.createElement('span');
            span.textContent = (f.NAME || f.PRODUCT) + ' — ' + (f.PRICE || '') + ' · ' + (f.STOCK || '');
            a.appendChild(span);
            wrap.appendChild(a);
        });
        log.appendChild(wrap);
        log.scrollTop = log.scrollHeight;
    }

    function send(text) {
        if (!text || sending) return;
        sending = true;
        bubble('user', text);
        push('user', text);
        var waiting = bubble('bot', isFr() ? 'Le concierge vérifie pour vous…' : 'The concierge is checking for you…');

        fetch('/api/concierge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
            },
            body: JSON.stringify({ messages: history, locale: isFr() ? 'fr' : 'en' })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                waiting.remove();
                renderCards(data.reply || '');
                push('assistant', data.reply || '');
            })
            .catch(function () {
                waiting.remove();
                bubble('bot', isFr()
                    ? 'Veuillez nous excuser, une interruption s\'est produite. Notre équipe est toujours disponible sur WhatsApp.'
                    : 'Forgive me, something interrupted us. Our team is always available on WhatsApp.');
            })
            .finally(function () { sending = false; });
    }

    if (btn && panel && log && form && input) {
        btn.addEventListener('click', function () {
            var opening = !panel.classList.contains('open');
            panel.classList.toggle('open', opening);
            if (opening && log.children.length === 0) {
                bubble('bot', input.dataset.greeting || (isFr()
                    ? 'Bonjour ! Comment puis-je vous aider aujourd\'hui ?'
                    : 'Good day! How may I assist you today?'));
            }
        });
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            send(input.value.trim());
            input.value = '';
        });
        document.querySelectorAll('.concierge-suggestions button').forEach(function (b) {
            b.addEventListener('click', function () { send(b.textContent.trim()); });
        });
    }

    // ── Product cards ─────────────────────────────────────────────────
    // Cards are plain links to /product/{slug} (server-rendered pages), so
    // no click interception here. An earlier empty [data-quick-view]
    // handler was removed: dead listeners make clicks look broken when
    // markup changes, and the hint chip is decorative only.
})();
