/**
 * sidebar-settings.js
 * Gestiona las preferencias de tema persistidas en localStorage:
 *   • Modo claro / oscuro     (data-bs-theme       en <html>)
 *   • Tamaño del sidebar      (data-sidenav-size   en <html>)
 *   • Color del topbar        (data-topbar-color   en <html>)
 *   • Color del sidebar/menu  (data-menu-color     en <html>)
 *   • Zoom de página          (zoom CSS            en <html>)
 *
 * Se carga en <head> para aplicar los valores antes del primer pintado
 * y evitar el "flash" de ajustes incorrectos.
 */
(function () {
    'use strict';

    // ── Valores por defecto ────────────────────────────────────────────────────
    var DEFAULT_SIZE         = 'default';
    var DEFAULT_THEME        = 'light';
    var DEFAULT_TOPBAR_COLOR = 'light';
    var DEFAULT_MENU_COLOR   = 'light';
    var DEFAULT_ZOOM         = 100;
    var ZOOM_STEP            = 10;
    var ZOOM_MIN             = 70;
    var ZOOM_MAX             = 130;

    // ── Claves de localStorage ─────────────────────────────────────────────────
    var STORAGE_SIZE         = 'zircos.sidenav-size';
    var STORAGE_THEME        = 'zircos.bs-theme';
    var STORAGE_TOPBAR_COLOR = 'zircos.topbar-color';
    var STORAGE_MENU_COLOR   = 'zircos.menu-color';
    var STORAGE_ZOOM         = 'zircos.page-zoom';
    var STORAGE_CONFIG       = '__ZIRCOS_CONFIG__';

    var htmlEl = document.documentElement;

    // ── Helpers ────────────────────────────────────────────────────────────────
    function applySize(size) {
        htmlEl.setAttribute('data-sidenav-size', size);
    }

    function applyTheme(theme) {
        htmlEl.setAttribute('data-bs-theme', theme);
    }

    function applyTopbarColor(color) {
        htmlEl.setAttribute('data-topbar-color', color);
    }

    function applyMenuColor(color) {
        htmlEl.setAttribute('data-menu-color', color);
    }

    function updateThemeToggleIcon(theme) {
        var btn = document.getElementById('light-dark-mode');
        if (!btn) return;

        var icon = btn.querySelector('i');
        if (!icon) return;

        icon.classList.remove('ti-moon', 'ti-sun');
        icon.classList.add(theme === 'dark' ? 'ti-sun' : 'ti-moon');
    }

    function syncThemeConfigFromDom() {
        var rawConfig = null;

        try {
            rawConfig = JSON.parse(sessionStorage.getItem(STORAGE_CONFIG) || 'null');
        } catch (error) {
            rawConfig = null;
        }

        var nextConfig = rawConfig && typeof rawConfig === 'object' ? rawConfig : {};

        nextConfig.theme = htmlEl.getAttribute('data-bs-theme') || DEFAULT_THEME;
        nextConfig.layout = nextConfig.layout && typeof nextConfig.layout === 'object' ? nextConfig.layout : {};
        nextConfig.layout.mode = nextConfig.layout.mode || 'fluid';
        nextConfig.topbar = nextConfig.topbar && typeof nextConfig.topbar === 'object' ? nextConfig.topbar : {};
        nextConfig.topbar.color = htmlEl.getAttribute('data-topbar-color') || DEFAULT_TOPBAR_COLOR;
        nextConfig.menu = nextConfig.menu && typeof nextConfig.menu === 'object' ? nextConfig.menu : {};
        nextConfig.menu.color = htmlEl.getAttribute('data-menu-color') || DEFAULT_MENU_COLOR;
        nextConfig.sidenav = nextConfig.sidenav && typeof nextConfig.sidenav === 'object' ? nextConfig.sidenav : {};
        nextConfig.sidenav.size = htmlEl.getAttribute('data-sidenav-size') || DEFAULT_SIZE;

        window.config = nextConfig;
        sessionStorage.setItem(STORAGE_CONFIG, JSON.stringify(nextConfig));
        updateThemeToggleIcon(nextConfig.theme);
    }

    function clampZoom(z) {
        return Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, z));
    }

    function applyZoom(zoom) {
        htmlEl.style.zoom = zoom + '%';
    }

    function updateZoomDisplay(zoom) {
        var el = document.getElementById('zoom-display');
        if (el) el.textContent = zoom + '%';
    }

    function getStoredZoom() {
        var z = parseInt(localStorage.getItem(STORAGE_ZOOM), 10);
        return isNaN(z) ? DEFAULT_ZOOM : z;
    }

    // ── Aplicar preferencias guardadas (síncrono, antes del primer paint) ──────
    var storedSize         = localStorage.getItem(STORAGE_SIZE);
    var storedTheme        = localStorage.getItem(STORAGE_THEME);
    var storedTopbarColor  = localStorage.getItem(STORAGE_TOPBAR_COLOR);
    var storedMenuColor    = localStorage.getItem(STORAGE_MENU_COLOR);
    var storedZoom         = getStoredZoom();

    if (storedSize)                      applySize(storedSize);
    if (storedTheme)                     applyTheme(storedTheme);
    if (storedTopbarColor)               applyTopbarColor(storedTopbarColor);
    if (storedMenuColor)                 applyMenuColor(storedMenuColor);
    if (storedZoom !== DEFAULT_ZOOM)     applyZoom(storedZoom);
    syncThemeConfigFromDom();

    // ── Conectar la UI cuando el DOM esté listo ────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        var currentSize         = htmlEl.getAttribute('data-sidenav-size')  || DEFAULT_SIZE;
        var currentTheme        = htmlEl.getAttribute('data-bs-theme')       || DEFAULT_THEME;
        var currentTopbarColor  = htmlEl.getAttribute('data-topbar-color')   || DEFAULT_TOPBAR_COLOR;
        var currentMenuColor    = htmlEl.getAttribute('data-menu-color')     || DEFAULT_MENU_COLOR;
        var currentZoom         = getStoredZoom();

        // ── Sincronizar radios al estado actual ───────────────────────────────
        function checkRadio(name, value) {
            var el = document.querySelector('input[name="' + name + '"][value="' + value + '"]');
            if (el) el.checked = true;
        }

        checkRadio('data-sidenav-size', currentSize);
        checkRadio('data-bs-theme',     currentTheme);
        checkRadio('data-topbar-color', currentTopbarColor);
        checkRadio('data-menu-color',   currentMenuColor);

        updateZoomDisplay(currentZoom);
        updateThemeToggleIcon(currentTheme);
        syncThemeConfigFromDom();

        var observer = new MutationObserver(function (mutations) {
            var shouldSync = mutations.some(function (mutation) {
                return mutation.type === 'attributes';
            });

            if (!shouldSync) return;

            var activeTheme = htmlEl.getAttribute('data-bs-theme') || DEFAULT_THEME;
            var activeSize = htmlEl.getAttribute('data-sidenav-size') || DEFAULT_SIZE;
            var activeTopbarColor = htmlEl.getAttribute('data-topbar-color') || DEFAULT_TOPBAR_COLOR;
            var activeMenuColor = htmlEl.getAttribute('data-menu-color') || DEFAULT_MENU_COLOR;

            localStorage.setItem(STORAGE_THEME, activeTheme);
            localStorage.setItem(STORAGE_SIZE, activeSize);
            localStorage.setItem(STORAGE_TOPBAR_COLOR, activeTopbarColor);
            localStorage.setItem(STORAGE_MENU_COLOR, activeMenuColor);

            checkRadio('data-bs-theme', activeTheme);
            checkRadio('data-sidenav-size', activeSize);
            checkRadio('data-topbar-color', activeTopbarColor);
            checkRadio('data-menu-color', activeMenuColor);

            syncThemeConfigFromDom();
        });

        observer.observe(htmlEl, {
            attributes: true,
            attributeFilter: [
                'data-bs-theme',
                'data-sidenav-size',
                'data-topbar-color',
                'data-menu-color'
            ]
        });

        // ── Función genérica para escuchar cambios en un grupo de radios ──────
        function listenRadios(name, applyFn, storageKey) {
            document.querySelectorAll('input[name="' + name + '"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    applyFn(this.value);
                    localStorage.setItem(storageKey, this.value);
                    syncThemeConfigFromDom();
                });
            });
        }

        listenRadios('data-sidenav-size', applySize,        STORAGE_SIZE);
        listenRadios('data-bs-theme',     applyTheme,       STORAGE_THEME);
        listenRadios('data-topbar-color', applyTopbarColor, STORAGE_TOPBAR_COLOR);
        listenRadios('data-menu-color',   applyMenuColor,   STORAGE_MENU_COLOR);

        // ── Zoom: botón "+" ───────────────────────────────────────────────────
        var zoomInBtn = document.getElementById('zoom-in');
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', function () {
                var z = clampZoom(getStoredZoom() + ZOOM_STEP);
                applyZoom(z);
                localStorage.setItem(STORAGE_ZOOM, z);
                updateZoomDisplay(z);
            });
        }

        // ── Zoom: botón "-" ───────────────────────────────────────────────────
        var zoomOutBtn = document.getElementById('zoom-out');
        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', function () {
                var z = clampZoom(getStoredZoom() - ZOOM_STEP);
                applyZoom(z);
                localStorage.setItem(STORAGE_ZOOM, z);
                updateZoomDisplay(z);
            });
        }

        // ── Zoom: botón "100%" ────────────────────────────────────────────────
        var zoomResetBtn = document.getElementById('zoom-reset');
        if (zoomResetBtn) {
            zoomResetBtn.addEventListener('click', function () {
                applyZoom(DEFAULT_ZOOM);
                localStorage.setItem(STORAGE_ZOOM, DEFAULT_ZOOM);
                updateZoomDisplay(DEFAULT_ZOOM);
            });
        }

        // ── Restablecer todo ──────────────────────────────────────────────────
        var resetBtn = document.getElementById('reset-theme-settings');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                // Limpiar storage
                [STORAGE_SIZE, STORAGE_THEME, STORAGE_TOPBAR_COLOR,
                 STORAGE_MENU_COLOR, STORAGE_ZOOM].forEach(function (key) {
                    localStorage.removeItem(key);
                });

                // Aplicar defaults
                applySize(DEFAULT_SIZE);
                applyTheme(DEFAULT_THEME);
                applyTopbarColor(DEFAULT_TOPBAR_COLOR);
                applyMenuColor(DEFAULT_MENU_COLOR);
                applyZoom(DEFAULT_ZOOM);
                updateZoomDisplay(DEFAULT_ZOOM);
                syncThemeConfigFromDom();

                // Sincronizar radios
                checkRadio('data-sidenav-size', DEFAULT_SIZE);
                checkRadio('data-bs-theme',     DEFAULT_THEME);
                checkRadio('data-topbar-color', DEFAULT_TOPBAR_COLOR);
                checkRadio('data-menu-color',   DEFAULT_MENU_COLOR);
            });
        }
    });

})();
