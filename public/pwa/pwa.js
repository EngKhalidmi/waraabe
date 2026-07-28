(function () {
    'use strict';

    var config = window.__PWA_CONFIG__ || {};
    var state = {
        deferredPrompt: null,
        installed: window.matchMedia && window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true
    };

    function injectStyles() {
        if (document.getElementById('pwa-status-style')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'pwa-status-style';
        style.textContent = [
            '#pwa-status-widget{position:fixed;right:16px;bottom:16px;z-index:2147483647;display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:16px;background:rgba(17,24,39,.94);color:#fff;box-shadow:0 18px 45px rgba(15,23,42,.24);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);font-family:Arial,Helvetica,sans-serif;max-width:min(100vw - 32px, 420px)}',
            '#pwa-status-widget[data-mode="offline"]{background:rgba(185,28,28,.96)}',
            '#pwa-status-widget[data-mode="online"]{background:rgba(21,128,61,.96)}',
            '#pwa-status-widget[data-mode="installable"]{background:rgba(61,94,225,.96)}',
            '#pwa-status-widget[data-mode="installed"]{background:rgba(17,24,39,.94)}',
            '#pwa-status-copy{min-width:0;display:flex;flex-direction:column;gap:2px;flex:1 1 auto}',
            '#pwa-status-title{font-size:14px;font-weight:700;line-height:1.2}',
            '#pwa-status-subtitle{font-size:12px;opacity:.82;line-height:1.3}',
            '#pwa-install-button{flex:0 0 auto;border:0;border-radius:12px;padding:10px 12px;background:#fff;color:#111827;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap}',
            '#pwa-install-button[hidden]{display:none !important}',
            '#pwa-status-dot{width:10px;height:10px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 5px rgba(255,255,255,.12)}',
            '#pwa-status-widget[data-mode="offline"] #pwa-status-dot{background:#fca5a5}',
            '#pwa-status-widget[data-mode="installable"] #pwa-status-dot{background:#dbeafe}',
            '#pwa-status-widget[data-mode="installed"] #pwa-status-dot{background:#93c5fd}',
            '@media (max-width: 640px){#pwa-status-widget{left:16px;right:16px;bottom:16px;max-width:none}}'
        ].join('');

        document.head.appendChild(style);
    }

    function ensureWidget() {
        if (document.getElementById('pwa-status-widget')) {
            return;
        }

        var widget = document.createElement('div');
        widget.id = 'pwa-status-widget';
        widget.setAttribute('role', 'status');
        widget.setAttribute('aria-live', 'polite');
        widget.innerHTML = [
            '<span id="pwa-status-dot" aria-hidden="true"></span>',
            '<div id="pwa-status-copy">',
            '  <strong id="pwa-status-title">Checking connection</strong>',
            '  <span id="pwa-status-subtitle">Preparing offline support</span>',
            '</div>',
            '<button id="pwa-install-button" type="button" hidden>Install app</button>'
        ].join('');

        document.body.appendChild(widget);
    }

    function updateWidget(mode, title, subtitle, showInstall) {
        var widget = document.getElementById('pwa-status-widget');
        var titleNode = document.getElementById('pwa-status-title');
        var subtitleNode = document.getElementById('pwa-status-subtitle');
        var installButton = document.getElementById('pwa-install-button');

        if (!widget || !titleNode || !subtitleNode || !installButton) {
            return;
        }

        widget.setAttribute('data-mode', mode);
        titleNode.textContent = title;
        subtitleNode.textContent = subtitle;
        installButton.hidden = !showInstall;
    }

    function syncStatus() {
        var online = navigator.onLine;
        var installable = Boolean(state.deferredPrompt) && !state.installed;

        if (state.installed) {
            updateWidget(
                'installed',
                'Installed',
                online ? 'Running as an app' : 'Installed and offline-ready',
                false
            );
            return;
        }

        if (online) {
            updateWidget(
                installable ? 'installable' : 'online',
                installable ? 'App ready to install' : 'Online',
                installable ? 'Tap install to add it to this device' : 'Connected to Laravel',
                installable
            );
            return;
        }

        updateWidget(
            'offline',
            'Offline',
            'Working from cached pages and assets',
            false
        );
    }

    function wireInstallPrompt() {
        var installButton = document.getElementById('pwa-install-button');

        if (!installButton) {
            return;
        }

        installButton.addEventListener('click', function () {
            if (!state.deferredPrompt) {
                return;
            }

            state.deferredPrompt.prompt();
            state.deferredPrompt.userChoice.then(function (choice) {
                if (choice && choice.outcome === 'accepted') {
                    state.installed = true;
                }

                state.deferredPrompt = null;
                syncStatus();
            }).catch(function () {
                state.deferredPrompt = null;
                syncStatus();
            });
        });
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            return;
        }

        navigator.serviceWorker.register(config.serviceWorkerUrl || '/service-worker.js').catch(function (error) {
            console.warn('Service worker registration failed:', error);
        });
    }

    function init() {
        injectStyles();
        ensureWidget();
        wireInstallPrompt();
        syncStatus();
        registerServiceWorker();
    }

    window.addEventListener('online', syncStatus);
    window.addEventListener('offline', syncStatus);
    window.addEventListener('appinstalled', function () {
        state.installed = true;
        state.deferredPrompt = null;
        syncStatus();
    });
    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        state.deferredPrompt = event;
        syncStatus();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
