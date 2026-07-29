(function () {
    'use strict';

    var config = window.__PWA_CONFIG__ || {};

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
        registerServiceWorker();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
