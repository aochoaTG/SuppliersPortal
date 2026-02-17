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

        // ── Función genérica para escuchar cambios en un grupo de radios ──────
        function listenRadios(name, applyFn, storageKey) {
            document.querySelectorAll('input[name="' + name + '"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    applyFn(this.value);
                    localStorage.setItem(storageKey, this.value);
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

                // Sincronizar radios
                checkRadio('data-sidenav-size', DEFAULT_SIZE);
                checkRadio('data-bs-theme',     DEFAULT_THEME);
                checkRadio('data-topbar-color', DEFAULT_TOPBAR_COLOR);
                checkRadio('data-menu-color',   DEFAULT_MENU_COLOR);
            });
        }
    });

})();
