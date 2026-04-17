# Supplier Register Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reemplazar los estilos visuales de `supplier-register.blade.php` con un diseño profesional Bootstrap 5 + TotalGas sin tocar la lógica del formulario.

**Architecture:** Un solo archivo blade modificado. Solo cambia CSS/HTML visual — todos los atributos `name`, `id`, `data-*` y bloques `<script>` quedan intactos, con la excepción mínima de 4 strings de className en el IIFE (solo presentación).

**Tech Stack:** Laravel Blade, Poppins (Google Fonts), CSS custom con variables TotalGas, Bootstrap 5 no se importa (clases propias inspiradas en BS5).

---

## Archivos

- Modify: `resources/views/auth/supplier-register.blade.php` (único archivo)

---

### Task 1: Reemplazar `<head>` — fuente y CSS

**Files:**
- Modify: `resources/views/auth/supplier-register.blade.php`

- [ ] **Step 1: Abrir el archivo y localizar el bloque `<head>`**

El `<head>` actual (líneas 1–830) contiene:
- `<link href="https://fonts.bunny.net/css?family=figtree...">` — eliminar
- Dos bloques `<style>` grandes — eliminar ambos
- El `@vite(...)` — **mantener** (el JS usa la clase `hidden` de Tailwind)
- Un bloque `<script>` con la lógica REPSE — **mantener intacto**

- [ ] **Step 2: Reemplazar el contenido del `<head>`**

Reemplazar todo el `<head>` con:

```blade
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Registro de Proveedor</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== VARIABLES ===== */
        :root {
            --tg-blue:    #1a4b96;
            --tg-blue-dk: #0d2b5e;
            --tg-green:   #A9CA48;
        }

        /* ===== PAGE ===== */
        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--tg-blue-dk) 0%, var(--tg-blue) 50%, var(--tg-blue-dk) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(169,202,72,.07);
            pointer-events: none;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -100px; left: -80px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(169,202,72,.05);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== CARD ===== */
        .register-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 780px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
            overflow: hidden;
        }

        /* ===== TOP BAR ===== */
        .card-topbar {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 24px;
            background: #fff;
            border-bottom: 3px solid var(--tg-green);
        }

        .topbar-logo  { height: 44px; width: auto; display: block; flex-shrink: 0; }
        .topbar-divider { width: 1px; height: 32px; background: #e9ecef; flex-shrink: 0; }
        .topbar-title { flex: 1; }
        .topbar-title h1 { font-size: 15px; font-weight: 700; color: var(--tg-blue); line-height: 1.2; margin: 0; }
        .topbar-title p  { font-size: 11px; color: #6c757d; margin: 0; }

        .step-indicator { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

        .step-dot {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            transition: background .25s, border-color .25s;
        }
        .step-dot.active   { background: var(--tg-green); color: #fff; border: 2px solid var(--tg-green); }
        .step-dot.inactive { background: #fff; color: #adb5bd; border: 2px solid #dee2e6; }

        .step-line { width: 28px; height: 2px; background: #dee2e6; }

        #label-1, #label-2 { font-size: 11px; font-weight: 500; color: #adb5bd; transition: color .25s; }
        #label-1.active, #label-2.active { color: var(--tg-green); }

        /* ===== STEPS ===== */
        .step-section          { display: none; }
        .step-section.active   { display: block; animation: fadeInSlide .35s ease-out; }

        @keyframes fadeInSlide {
            from { opacity: 0; transform: translateX(14px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ===== FORM BODY ===== */
        .card-body { padding: 24px; }

        .section-title {
            font-size: 13px; font-weight: 600; color: #212529;
            border-left: 3px solid var(--tg-green);
            padding-left: 10px; margin-bottom: 18px;
        }

        .form-row      { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .form-row.full { grid-template-columns: 1fr; }
        .form-group    { margin-bottom: 0; }

        .form-label { display: block; font-size: 12px; font-weight: 500; color: #495057; margin-bottom: 5px; }

        .form-control {
            width: 100%;
            border: 1.5px solid #dee2e6;
            border-radius: 7px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #495057;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--tg-green);
            box-shadow: 0 0 0 3px rgba(169,202,72,.15);
        }

        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24'%3E%3Cpath fill='%23666' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }

        textarea.form-control { resize: vertical; min-height: 90px; }

        .input-hint  { font-size: 11px; color: #adb5bd; margin-top: 4px; }
        .input-error { font-size: 12px; color: #dc2626; background: #fff5f5; border: 1px solid #fecaca; border-radius: 6px; padding: 6px 10px; margin-top: 5px; }

        /* ===== RADIO BUTTONS (REPSE) ===== */
        .radio-group  { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 4px; }
        .radio-option { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #495057; user-select: none; }
        .radio-option input[type="radio"] { display: none; }
        .radio-custom {
            width: 18px; height: 18px;
            border-radius: 50%;
            border: 2px solid #dee2e6;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; transition: border-color .2s;
        }
        .radio-option input[type="radio"]:checked + .radio-custom { border-color: var(--tg-green); }
        .radio-option input[type="radio"]:checked + .radio-custom::after {
            content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--tg-green);
        }

        /* ===== MULTISELECT (REPSE) ===== */
        .custom-multiselect { position: relative; width: 100%; }
        .multiselect-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 12px;
            border: 1.5px solid #dee2e6; border-radius: 7px;
            background: #fff; cursor: pointer;
            font-size: 13px; color: #6c757d;
            transition: border-color .2s;
        }
        .multiselect-header:hover { border-color: #adb5bd; }
        .multiselect-header.active { border-color: var(--tg-green); }
        .dropdown-arrow { font-size: 11px; color: #6c757d; transition: transform .2s; }
        .multiselect-header.active .dropdown-arrow { transform: rotate(180deg); }
        .multiselect-options {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #fff;
            border: 1.5px solid #dee2e6; border-top: none;
            border-radius: 0 0 7px 7px;
            max-height: 200px; overflow-y: auto;
            z-index: 1000; display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
        }
        .multiselect-options.show { display: block; }
        .multiselect-option {
            display: flex; align-items: center;
            padding: 10px 12px; cursor: pointer;
            font-size: 13px; color: #495057;
            border-bottom: 1px solid #f3f4f6;
            transition: background .15s;
        }
        .multiselect-option:hover { background: #f8f9fa; }
        .multiselect-option:last-child { border-bottom: none; }
        .multiselect-option input[type="checkbox"] { display: none; }
        .checkmark {
            width: 16px; height: 16px;
            border: 1.5px solid #dee2e6; border-radius: 4px;
            margin-right: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: all .15s;
        }
        .multiselect-option input[type="checkbox"]:checked + .checkmark { background: var(--tg-green); border-color: var(--tg-green); }
        .multiselect-option input[type="checkbox"]:checked + .checkmark::after { content: '✓'; color: #fff; font-size: 10px; font-weight: 700; }

        .repse-container { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; margin-top: 4px; }

        /* ===== CARD FOOTER ===== */
        .card-footer-bar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 24px;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        /* ===== BUTTONS ===== */
        .btn-primary-tg {
            background: var(--tg-blue); color: #fff;
            border: none; border-radius: 8px;
            padding: 10px 24px;
            font-size: 13px; font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            transition: background .2s;
        }
        .btn-primary-tg:hover    { background: #163d7a; }
        .btn-primary-tg:disabled { opacity: .6; cursor: not-allowed; }

        .btn-secondary-tg {
            background: #fff; color: #6c757d;
            border: 1.5px solid #dee2e6; border-radius: 8px;
            padding: 10px 20px;
            font-size: 13px; font-weight: 500;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: border-color .2s, color .2s;
        }
        .btn-secondary-tg:hover { border-color: var(--tg-blue); color: var(--tg-blue); }

        .arrow-accent { color: var(--tg-green); font-size: 15px; }

        /* ===== LINKS ===== */
        .link-secondary { font-size: 12px; color: var(--tg-blue); text-decoration: none; transition: color .2s; }
        .link-secondary:hover { color: var(--tg-green); }

        /* ===== DATE STATES (REPSE validation) ===== */
        .date-warning { border-color: #f59e0b !important; background: #fef3c7; }
        .date-error   { border-color: #ef4444 !important; background: #fee2e2; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            body { padding: 16px; }
            .card-topbar { flex-wrap: wrap; padding: 12px 16px; }
            .step-indicator { margin-left: auto; }
            .card-body { padding: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .card-footer-bar { flex-direction: column-reverse; gap: 10px; padding: 12px 16px; }
            .btn-primary-tg, .btn-secondary-tg { width: 100%; justify-content: center; }
        }
    </style>

    <script>
        {{-- BLOQUE REPSE — NO MODIFICAR — mantener igual que el original --}}
    </script>
</head>
```

> ⚠️ El bloque `<script>` del head (REPSE toggle) va DENTRO del nuevo head. Cópialo del archivo original sin cambiar ni una línea.

---

### Task 2: Reemplazar estructura del `<body>`

**Files:**
- Modify: `resources/views/auth/supplier-register.blade.php`

- [ ] **Step 1: Reemplazar el body — wrapper y top-bar**

Reemplazar desde `<body class="font-sans antialiased">` hasta el `<form ...>` con:

```blade
<body>
    <div class="register-card">

        {{-- TOP BAR --}}
        <div class="card-topbar">
            <img src="{{ asset('images/logos/logo_TotalGas_ver.png') }}"
                 alt="TotalGas"
                 class="topbar-logo">
            <div class="topbar-divider"></div>
            <div class="topbar-title">
                <h1>Registro de Proveedor</h1>
                <p>Complete los datos para crear su cuenta empresarial</p>
            </div>
            <div class="step-indicator">
                <div id="dot-1" class="step-dot active">1</div>
                <span id="label-1" class="active">Cuenta</span>
                <div class="step-line"></div>
                <div id="dot-2" class="step-dot inactive">2</div>
                <span id="label-2">Empresa</span>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" id="supplier-form">
            @csrf
            {{-- Elemento oculto para compatibilidad con el JS que lee progress-fill --}}
            <div id="progress-fill" style="display:none;"></div>
            <div id="bar"           style="display:none;"></div>
```

- [ ] **Step 2: Reemplazar Step 1 — datos de cuenta**

Reemplazar el `<div data-step="1" class="step-section active">` completo con:

```blade
            {{-- STEP 1 --}}
            <div data-step="1" class="step-section active">
                <div class="card-body">
                    <div class="section-title">Datos de la cuenta</div>

                    <div class="form-row">
                        <div class="form-group">
                            <x-input-label for="first_name" :value="__('Nombre(s)')" class="form-label" />
                            <x-text-input id="first_name" class="form-control" type="text"
                                name="first_name" :value="old('first_name')"
                                data-required="1" autofocus autocomplete="given-name" />
                            <x-input-error :messages="$errors->get('first_name')" class="input-error" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="last_name" :value="__('Apellidos')" class="form-label" />
                            <x-text-input id="last_name" class="form-control" type="text"
                                name="last_name" :value="old('last_name')"
                                data-required="1" autocomplete="family-name" />
                            <x-input-error :messages="$errors->get('last_name')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <x-input-label for="email" :value="__('Correo electrónico')" class="form-label" />
                            <x-text-input id="email" class="form-control" type="email"
                                name="email" :value="old('email')"
                                data-required="1" autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <x-input-label for="password" :value="__('Contraseña')" class="form-label" />
                            <x-text-input id="password" class="form-control" type="password"
                                name="password" data-required="1" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="input-error" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="form-label" />
                            <x-text-input id="password_confirmation" class="form-control" type="password"
                                name="password_confirmation" data-required="1" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="input-error" />
                        </div>
                    </div>
                </div>

                <div class="card-footer-bar">
                    <a class="link-secondary" href="{{ route('login') }}">
                        {{ __('¿Ya tienes cuenta? Inicia sesión') }}
                    </a>
                    <button type="button" id="next-btn" class="btn-primary-tg">
                        {{ __('Siguiente') }} <span class="arrow-accent">→</span>
                    </button>
                </div>
            </div>
```

- [ ] **Step 3: Reemplazar Step 2 — datos de empresa**

Reemplazar el `<div data-step="2" class="step-section">` completo con:

```blade
            {{-- STEP 2 --}}
            <div data-step="2" class="step-section">
                <div class="card-body">
                    <div class="section-title">Datos del proveedor</div>

                    <div class="form-row full">
                        <div class="form-group">
                            <x-input-label for="company_name" :value="__('Razón social / Nombre comercial')" class="form-label" />
                            <x-text-input id="company_name" class="form-control" type="text"
                                name="company_name" :value="old('company_name')" data-required="1" />
                            <x-input-error :messages="$errors->get('company_name')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <x-input-label for="rfc" :value="__('RFC')" class="form-label" />
                            <x-text-input id="rfc" class="form-control" type="text"
                                name="rfc" :value="old('rfc')" data-required="1"
                                maxlength="13" style="text-transform:uppercase;"
                                inputmode="latin" autocomplete="off" />
                            <div class="input-hint">Formato: <strong>3–4 letras</strong> + <strong>6 dígitos (YYMMDD)</strong> + <strong>3 alfanuméricos</strong></div>
                            <x-input-error :messages="$errors->get('rfc')" class="input-error" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="phone_number" :value="__('Teléfono de la empresa')" class="form-label" />
                            <x-text-input id="phone_number" class="form-control" type="tel"
                                name="phone_number" :value="old('phone_number')" data-required="1"
                                data-phone="10" inputmode="numeric" pattern="\d{10}"
                                maxlength="10" minlength="10" autocomplete="tel" />
                            <div class="input-hint">Formato: <strong>10 dígitos exactos</strong></div>
                            <x-input-error :messages="$errors->get('phone_number')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <x-input-label for="contact_person" :value="__('Persona de contacto')" class="form-label" />
                            <x-text-input id="contact_person" class="form-control" type="text"
                                name="contact_person" :value="old('contact_person')" data-required="1" />
                            <x-input-error :messages="$errors->get('contact_person')" class="input-error" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="contact_phone" :value="__('Teléfono de contacto (opcional)')" class="form-label" />
                            <x-text-input id="contact_phone" class="form-control" type="tel"
                                name="contact_phone" :value="old('contact_phone')"
                                data-phone="10" inputmode="numeric" pattern="\d{10}"
                                maxlength="10" minlength="10" autocomplete="tel" />
                            <div class="input-hint">Formato: <strong>10 dígitos si se proporciona</strong></div>
                            <x-input-error :messages="$errors->get('contact_phone')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <x-input-label for="supplier_type" :value="__('Tipo de proveedor')" class="form-label" />
                            <select id="supplier_type" name="supplier_type" class="form-control" data-required="1">
                                <option value="" disabled {{ old('supplier_type') ? '' : 'selected' }}>Selecciona una opción</option>
                                <option value="product"         {{ old('supplier_type') === 'product'         ? 'selected' : '' }}>Productos</option>
                                <option value="service"         {{ old('supplier_type') === 'service'         ? 'selected' : '' }}>Servicios</option>
                                <option value="product_service" {{ old('supplier_type') === 'product_service' ? 'selected' : '' }}>Productos y Servicios</option>
                            </select>
                            <x-input-error :messages="$errors->get('supplier_type')" class="input-error" />
                        </div>
                        <div class="form-group">
                            <x-input-label for="tax_regime" :value="__('Régimen fiscal')" class="form-label" />
                            <select id="tax_regime" name="tax_regime" class="form-control" data-required="1">
                                <option value="" disabled {{ old('tax_regime') ? '' : 'selected' }}>Selecciona una opción</option>
                                <option value="individual"   {{ old('tax_regime') === 'individual'   ? 'selected' : '' }}>Persona Física</option>
                                <option value="corporation"  {{ old('tax_regime') === 'corporation'  ? 'selected' : '' }}>Persona Moral</option>
                                <option value="resico"       {{ old('tax_regime') === 'resico'       ? 'selected' : '' }}>RESICO</option>
                            </select>
                            <x-input-error :messages="$errors->get('tax_regime')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <x-input-label for="economic_activity" :value="__('Actividad económica')" class="form-label" />
                            <x-text-input id="economic_activity" class="form-control" type="text"
                                name="economic_activity" :value="old('economic_activity')" data-required="1" />
                            <div class="input-hint">Agrégala tal como aparece en la constancia fiscal (Actividad económica)</div>
                            <x-input-error :messages="$errors->get('economic_activity')" class="input-error" />
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <x-input-label for="default_payment_terms" :value="__('Condiciones de pago')" class="form-label" />
                            <select id="default_payment_terms" name="default_payment_terms" class="form-control" data-required="1">
                                @foreach(\App\Enum\PaymentTerm::options() as $value => $label)
                                    <option value="{{ $value }}" {{ old('default_payment_terms', 'CASH') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="input-hint">Condiciones de pago por defecto para OC y cotizaciones.</div>
                            <x-input-error :messages="$errors->get('default_payment_terms')" class="input-error" />
                        </div>
                    </div>

                    {{-- REPSE --}}
                    <div class="form-row full">
                        <div class="form-group">
                            <x-input-label for="provides_specialized_services" :value="__('¿Presta servicios especializados u obras especializadas?')" class="form-label" />
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="provides_specialized_services" value="1" id="repse_yes"
                                        {{ old('provides_specialized_services') === '1' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span> Sí
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="provides_specialized_services" value="0" id="repse_no"
                                        {{ old('provides_specialized_services') === '0' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span> No
                                </label>
                            </div>
                            <div class="input-hint">Los servicios especializados incluyen: limpieza, vigilancia, mantenimiento, contabilidad, etc.</div>
                            <x-input-error :messages="$errors->get('provides_specialized_services')" class="input-error" />
                        </div>
                    </div>

                    {{-- REPSE fields (hidden by default) --}}
                    <div id="repse-fields" class="form-group full-width repse-container" style="display:none;">
                        <div class="form-row">
                            <div class="form-group">
                                <x-input-label for="repse_registration_number" :value="__('Número de Registro REPSE')" class="form-label" />
                                <x-text-input id="repse_registration_number" class="form-control" type="text"
                                    name="repse_registration_number" :value="old('repse_registration_number')"
                                    placeholder="Ej: REPSE-123456789" data-conditional-required="repse" />
                                <div class="input-hint">Formato: REPSE seguido del número asignado</div>
                                <x-input-error :messages="$errors->get('repse_registration_number')" class="input-error" />
                            </div>
                            <div class="form-group">
                                <x-input-label for="repse_expiry_date" :value="__('Fecha de vencimiento REPSE')" class="form-label" />
                                <x-text-input id="repse_expiry_date" class="form-control" type="date"
                                    name="repse_expiry_date" :value="old('repse_expiry_date')"
                                    data-conditional-required="repse" />
                                <div class="input-hint">El registro debe estar vigente</div>
                                <x-input-error :messages="$errors->get('repse_expiry_date')" class="input-error" />
                            </div>
                        </div>

                        <div class="form-row full">
                            <div class="form-group">
                                <x-input-label for="specialized_services_dropdown" :value="__('Tipos de servicios especializados')" class="form-label" />
                                <div class="custom-multiselect" id="custom-multiselect">
                                    <div class="multiselect-header" onclick="toggleDropdown()">
                                        <span id="selected-text">Seleccionar servicios...</span>
                                        <span class="dropdown-arrow">▼</span>
                                    </div>
                                    <div class="multiselect-options" id="multiselect-options">
                                        @foreach([
                                            'limpieza'     => 'Servicios de limpieza',
                                            'vigilancia'   => 'Vigilancia y seguridad',
                                            'mantenimiento'=> 'Mantenimiento',
                                            'alimentacion' => 'Servicios de alimentación',
                                            'contabilidad' => 'Servicios contables/administrativos',
                                            'sistemas'     => 'Servicios de sistemas/TI',
                                            'otros'        => 'Otros',
                                        ] as $val => $lbl)
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="{{ $val }}"
                                                {{ in_array($val, old('specialized_services_types', [])) ? 'checked' : '' }}
                                                {{ $val === 'otros' ? 'id="otros_checkbox_select"' : '' }}>
                                            <span class="checkmark"></span>
                                            {{ $lbl }}
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="input-hint">Puede seleccionar múltiples servicios</div>
                                <x-input-error :messages="$errors->get('specialized_services_types')" class="input-error" />
                            </div>
                        </div>

                        <div id="otros-input-custom" class="form-group" style="display:none;">
                            <x-input-label for="otros_descripcion_custom" :value="__('Especifique otros servicios')" class="form-label" />
                            <x-text-input id="otros_descripcion_custom" class="form-control" type="text"
                                name="otros_descripcion" :value="old('otros_descripcion')"
                                placeholder="Describa los otros servicios especializados..." />
                        </div>
                    </div>

                    <div class="form-row full" style="margin-top:14px;">
                        <div class="form-group">
                            <x-input-label for="address" :value="__('Domicilio fiscal')" class="form-label" />
                            <textarea id="address" name="address" class="form-control"
                                rows="3" data-required="1"
                                placeholder="Ingrese la dirección completa del domicilio fiscal">{{ old('address') }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="input-error" />
                        </div>
                    </div>
                </div>

                <div class="card-footer-bar">
                    <button type="button" id="back-btn" class="btn-secondary-tg">
                        ← {{ __('Atrás') }}
                    </button>
                    <button type="submit" class="btn-primary-tg">
                        {{ __('Registrar Proveedor') }}
                    </button>
                </div>
            </div>

        </form>
    </div>
```

- [ ] **Step 4: Cerrar el body correctamente**

Después de `</div>` (cierre de `.register-card`), pegar todos los bloques `<script>` del archivo original sin modificar ninguno, y cerrar con `</body></html>`.

---

### Task 3: Corregir className strings de dots en el IIFE

**Files:**
- Modify: `resources/views/auth/supplier-register.blade.php`

El IIFE en el segundo bloque `<script>` tiene una función `showStep` que sobrescribe las clases de `dot1` y `dot2` con strings de Tailwind. Esto hace que los dots pierdan los colores TotalGas al navegar entre pasos.

- [ ] **Step 1: Localizar las 4 líneas a cambiar**

Dentro del IIFE, buscar la función `showStep`. Contiene este bloque:

```js
if (step === 1) {
    bar.style.width = '0%';
    dot1.className = 'w-8 h-8 flex items-center justify-content: rounded-full border border-indigo-600 text-white bg-indigo-600';
    dot2.className = 'w-8 h-8 flex items-center justify-content: rounded-full border border-gray-300 text-gray-500';
} else {
    bar.style.width = '100%';
    dot1.className = 'w-8 h-8 flex items-center justify-content: rounded-full border border-indigo-600 text-indigo-600 bg-white';
    dot2.className = 'w-8 h-8 flex items-center justify-content: rounded-full border border-indigo-600 text-white bg-indigo-600';
}
```

- [ ] **Step 2: Reemplazar esas 4 líneas**

```js
if (step === 1) {
    bar.style.width = '0%';
    dot1.className = 'step-dot active';
    dot2.className = 'step-dot inactive';
} else {
    bar.style.width = '100%';
    dot1.className = 'step-dot inactive';
    dot2.className = 'step-dot active';
}
```

> Esto es presentación pura — los IDs, la lógica de pasos y `syncRequired()` no se tocan.

---

### Task 4: Verificar visualmente y commitear

**Files:**
- Modify: `resources/views/auth/supplier-register.blade.php`

- [ ] **Step 1: Abrir en el navegador**

Navegar a `http://localhost/register` (o la URL local del proyecto). Verificar:
- Fondo azul TotalGas estático ✓
- Logo `logo_TotalGas_ver.png` visible en top-bar ✓
- Borde verde lima bajo el top-bar ✓
- Step indicator muestra "1 — Cuenta" activo (verde) y "2 — Empresa" inactivo (gris) ✓
- Inputs con focus ring verde lima al hacer clic ✓
- Botón "Siguiente" azul oscuro con flecha verde ✓

- [ ] **Step 2: Navegar al paso 2**

Hacer clic en "Siguiente" (con campos vacíos para ver validación, luego con datos válidos). Verificar:
- El step indicator cambia: "1" pasa a inactivo, "2" pasa a verde ✓
- El formulario del paso 2 aparece con animación suave ✓
- Botón "Atrás" visible y funcional ✓
- Los campos REPSE se muestran/ocultan al seleccionar Sí/No ✓

- [ ] **Step 3: Commitear**

```bash
git add resources/views/auth/supplier-register.blade.php
git commit -m "feat: rediseño visual supplier-register con estilo TotalGas/Bootstrap5"
```

---

## Self-Review

**Cobertura del spec:**
- ✅ Fondo gradiente azul estático con círculos decorativos → Task 1 (CSS body/::before/::after)
- ✅ Card blanca max-width 780px → Task 1 (.register-card)
- ✅ Top-bar con logo, divisor, título, step-indicator, borde verde → Tasks 1+2
- ✅ Inputs con focus verde lima → Task 1 (.form-control:focus)
- ✅ Buttons primario azul / secundario blanco → Task 1
- ✅ Radio buttons REPSE con punto verde → Task 1
- ✅ Multiselect checkmarks verde → Task 1
- ✅ Animaciones eliminadas (parallax, shimmer, cardFloat) → Task 1 (no incluidas en nuevo CSS)
- ✅ `fadeInSlide` conservado → Task 1
- ✅ Dot className fix → Task 3
- ✅ Font Poppins → Task 1
- ✅ Todos los atributos de formulario intactos → Tasks 2 (código literal copiado con mismos attrs)
- ✅ Responsive mobile → Task 1 (@media 640px)
