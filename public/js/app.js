// Mobile menu
document.querySelector('[data-menu]')?.addEventListener('click', () => document.getElementById('nav').classList.toggle('open'));

// Confirm before risky actions, and stop double submits (a double tap must not record two balls).
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.dataset.confirm && !confirm(form.dataset.confirm)) { e.preventDefault(); return; }
    if (form.dataset.sent) { e.preventDefault(); return; }
    form.dataset.sent = '1';
});
window.addEventListener('pageshow', () => document.querySelectorAll('form[data-sent]').forEach((f) => delete f.dataset.sent));

// Live refresh for auction and score pages
document.querySelectorAll('[data-refresh]').forEach((box) => {
    const load = async () => {
        if (!document.hidden) {
            try {
                const res = await fetch(box.dataset.refresh, { cache: 'no-store' });
                if (res.ok) box.innerHTML = await res.text();
            } catch (err) { /* network drop: try again on the next tick */ }
        }
        setTimeout(load, Number(box.dataset.every) || 4000);
    };
    setTimeout(load, Number(box.dataset.every) || 4000);
});

// Countdown to the first match day
document.querySelectorAll('[data-countdown]').forEach((el) => {
    const target = new Date(el.dataset.countdown).getTime();
    const tick = () => {
        let s = Math.floor((target - Date.now()) / 1000);
        if (s <= 0) { el.innerHTML = ''; return; }
        const parts = [['Days', 86400], ['Hours', 3600], ['Mins', 60], ['Secs', 1]].map(([label, size]) => {
            const v = Math.floor(s / size); s %= size;
            return `<div><b>${String(v).padStart(2, '0')}</b><small>${label}</small></div>`;
        });
        el.innerHTML = parts.join('');
        setTimeout(tick, 1000);
    };
    tick();
});

// ---------- Phone app ----------
const appRoot = new URL('../', document.currentScript.src);
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(new URL('sw.js', appRoot)).catch(() => { /* website still works without it */ });
}

(() => {
    const box = document.getElementById('install');
    if (!box || matchMedia('(display-mode: standalone)').matches || navigator.standalone) return;
    // Storage can be blocked in private windows, so never depend on it.
    const dismissed = () => { try { return localStorage.getItem('installClosed') === '1'; } catch (e) { return false; } };
    if (dismissed()) return;

    const button = document.getElementById('install-btn');
    let prompt = null;
    // Android / desktop Chrome tells us when installing is possible.
    window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); prompt = e; box.hidden = false; });
    // iPhone has no install button, so show the steps instead.
    if (/iphone|ipad|ipod/i.test(navigator.userAgent)) {
        document.getElementById('install-help').textContent = 'Open in Safari, tap Share, then "Add to Home Screen".';
        button.hidden = true;
        box.hidden = false;
    }
    button.addEventListener('click', async () => {
        if (!prompt) return;
        prompt.prompt();
        await prompt.userChoice;
        prompt = null;
        box.hidden = true;
    });
    document.getElementById('install-close').addEventListener('click', () => {
        box.hidden = true;
        try { localStorage.setItem('installClosed', '1'); } catch (e) { /* ignore */ }
    });
    window.addEventListener('appinstalled', () => { box.hidden = true; });
})();
