# Spec: Rediseño Visual — Página de Registro de Proveedor

**Fecha:** 2026-04-17
**Archivo objetivo:** `resources/views/auth/supplier-register.blade.php`

---

## Objetivo

Reemplazar los estilos actuales de la página de registro de proveedor por un diseño profesional coherente con el template Zircos (Bootstrap 5) y la identidad visual de TotalGas, sin modificar ninguna funcionalidad del formulario (validaciones, pasos, lógica JS).

---

## Decisiones de diseño aprobadas

| Decisión | Elección |
|---|---|
| Layout | Card centrada con top-bar corporativa (opción C) |
| Fondo | Gradiente azul TotalGas estático (opción C) |
| Logo en top-bar | `public/images/logos/logo_TotalGas_ver.png` |

---

## Diseño

### Fondo de página

- `background: linear-gradient(135deg, #0d2b5e 0%, #1a4b96 50%, #0d2b5e 100%)`
- Estático — sin animación de gradiente
- Dos círculos decorativos `::before` / `::after` con `rgba(169,202,72, .07/.05)` en esquinas opuestas

### Card principal

- `max-width: 780px`, centrada con `margin: auto`
- `background: #fff`, `border-radius: 14px`
- `box-shadow: 0 20px 60px rgba(0,0,0,.35)`

### Top-bar (`card-topbar`)

- Flex row: `logo | divisor vertical | título+subtítulo | step indicator`
- Logo: `<img src="{{ asset('images/logos/logo_TotalGas_ver.png') }}" height="44px">`
- Divisor: `width:1px; height:32px; background:#e9ecef`
- Título: `font-size:15px; font-weight:700; color:#1a4b96`
- Subtítulo: `font-size:11px; color:#6c757d`
- **Borde inferior:** `3px solid #A9CA48`
- Step indicator (derecha): dos círculos 28×28px — activo `#A9CA48` blanco, inactivo borde gris; línea conectora `#dee2e6`; etiquetas 11px

### Cuerpo del formulario

- `padding: 24px`
- Títulos de sección: `border-left: 3px solid #A9CA48; padding-left: 10px; font-size:13px; font-weight:600`
- Grid de 2 columnas con `gap: 14px`; campos ancho completo con clase `full`
- Labels: `font-size:12px; font-weight:500; color:#495057`
- Inputs: `border: 1.5px solid #dee2e6; border-radius:7px; padding:9px 12px; font-size:13px`
- Focus: `border-color:#A9CA48; box-shadow: 0 0 0 3px rgba(169,202,72,.15)`
- Errores de validación: fondo `#fff5f5`, borde `#fecaca`, texto `#dc2626`, `border-radius:6px`
- Hints: `font-size:11px; color:#adb5bd`

### Footer del card

- `background:#fafafa; border-top:1px solid #f0f0f0; padding:14px 24px`
- Flex: enlace izquierda (`color:#1a4b96; font-size:12px`) | botón derecha
- Botón primario: `background:#1a4b96; color:#fff; border-radius:8px; padding:10px 24px`; flecha `→` en `color:#A9CA48`
- Botón secundario (Atrás): `background:#fff; border:1.5px solid #dee2e6; color:#6c757d`

### Multiselect REPSE

- Mantiene su estructura HTML y JS actual
- Estilos actualizados: borde `#dee2e6`, checkmarks en `#A9CA48`, hover `#f8f9fa`

### Radio buttons (servicios especializados)

- `.radio-group`: flex row con `gap: 16px`
- `.radio-option`: label clickable con cursor pointer, `font-size:13px; color:#495057`
- Input radio nativo oculto; `.radio-custom` como círculo custom `20×20px`, borde `#dee2e6`, checked → borde + punto interior `#A9CA48`

### Tipografía

- Font: **Poppins** (igual que login) — importar desde Google Fonts
- Eliminar Figtree/Bunny fonts

### Animaciones eliminadas

Los siguientes elementos del diseño actual se eliminan por generar ruido visual:
- `gradientShift` (animación del fondo)
- `cardFloat` (card flotante)
- `shimmer` del header
- Formas geométricas flotantes (`geometric-shape`)
- Efecto parallax con mousemove
- Efecto ripple en botones

Se conserva únicamente:
- `fadeInSlide` suave al cambiar de paso (opacidad + translateX sutil)
- Transición de `border-color` y `box-shadow` en inputs (0.2s)

---

## Restricciones

- **No modificar ningún atributo de formulario**: `name`, `id`, `data-required`, `data-phone`, `data-conditional-required`, `type`, `value`, `autocomplete`, `pattern`, `maxlength`
- **No modificar ningún bloque `<script>`** — todo el JS permanece intacto, **con una excepción**: las dos líneas en el IIFE que asignan clases Tailwind hardcodeadas a `dot1.className` y `dot2.className` (`'w-8 h-8 flex items-center...'`) se actualizan a `'step-dot active'` / `'step-dot inactive'` para que los dots mantengan los colores TotalGas tras cada cambio de paso. Esto es presentación pura, no lógica.
- **No modificar la estructura de pasos** (`data-step="1"`, `data-step="2"`, clases `step-section`, `active`, `hidden`)
- **No modificar los IDs** usados por JS: `next-btn`, `back-btn`, `dot-1`, `dot-2`, `progress-fill`, `label-1`, `label-2`, `repse-fields`, `repse_yes`, `repse_no`, `custom-multiselect`, etc.

---

## Alcance exacto del cambio

Solo se reemplaza:
1. El bloque `<head>` (meta, title, fonts, CSS en `<style>`)
2. El `<body>` en cuanto a clases CSS y estructura visual del wrapper (fondo, card, top-bar, footer)
3. Las clases en los inputs, labels y botones (de `professional-input` a `form-control`, etc.)

El contenido de los `<script>` al final del `<body>` no se toca.
