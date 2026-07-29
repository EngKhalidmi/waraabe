(function () {
    'use strict';

    var config = window.__PWA_CONFIG__ || {};
    var state = {
        deferredPrompt: null,
        installed: window.matchMedia && window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true
    };

    function updateWidget(mode, title, subtitle, showInstall) {
        return {
            mode: mode,
            title: title,
            subtitle: subtitle,
            showInstall: showInstall
        };
    }

    function syncStatus() {
        state.online = navigator.onLine;
        state.installable = Boolean(state.deferredPrompt) && !state.installed;
    }

    function wireInstallPrompt() {
        return;
    }

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            return;
        }

        if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            return;
        }

        navigator.serviceWorker.register(config.serviceWorkerUrl || new URL('service-worker.js', window.location.href).toString()).catch(function (error) {
            console.warn('Service worker registration failed:', error);
        });
    }

    function init() {
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
