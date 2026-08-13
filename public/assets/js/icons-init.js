/*
 * Renders Lucide icons and keeps them rendered for content injected later
 * (DataTables redraws, modals, AJAX rows, JS-built markup).
 *
 * Lucide keeps the data-lucide attribute on the SVG it creates, so the
 * placeholder lookup must exclude <svg> or the observer would re-trigger
 * itself forever.
 */
(function () {
    'use strict';

    var PLACEHOLDER_SELECTOR = '[data-lucide]:not(svg)';
    var frameRequested = false;

    function renderIcons() {
        if (!window.lucide || typeof window.lucide.createIcons !== 'function') {
            return;
        }

        try {
            window.lucide.createIcons();
        } catch (error) {
            console.warn('Lucide icon render failed:', error);
        }
    }

    function scheduleRender() {
        if (frameRequested) {
            return;
        }

        frameRequested = true;

        window.requestAnimationFrame(function () {
            frameRequested = false;

            if (document.querySelector(PLACEHOLDER_SELECTOR)) {
                renderIcons();
            }
        });
    }

    function start() {
        renderIcons();

        if (!('MutationObserver' in window)) {
            return;
        }

        new MutationObserver(function (mutations) {
            for (var index = 0; index < mutations.length; index++) {
                if (mutations[index].addedNodes.length) {
                    scheduleRender();
                    return;
                }
            }
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }

    /**
     * Swap the icon of an already-rendered element.
     * Lucide replaces the node, so callers should re-query by selector after.
     */
    function setIcon(target, iconName) {
        var element = typeof target === 'string' ? document.querySelector(target) : target;

        if (!element) {
            return null;
        }

        element.setAttribute('data-lucide', iconName);
        renderIcons();

        return typeof target === 'string' ? document.querySelector(target) : element;
    }

    // Exposed for code that wants to force a pass immediately after a DOM write.
    window.renderIcons = scheduleRender;
    window.setIcon = setIcon;
})();
