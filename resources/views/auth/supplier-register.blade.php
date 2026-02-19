{{-- resources/views/auth/supplier-register.blade.php - Corporate Style V3 --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Registro de Proveedor</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bundy.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Estilos para select múltiple tradicional */
        select[multiple] {
            min-height: 150px;
            padding: 8px;
        }

        select[multiple] option {
            padding: 8px;
            margin: 2px 0;
            border-radius: 4px;
        }

        select[multiple] option:checked {
            background: #4f46e5;
            color: white;
        }

        /* Estilos para multiselect personalizado */
        .custom-multiselect {
            position: relative;
            width: 100%;
        }

        .multiselect-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .multiselect-header:hover {
            border-color: #cbd5e1;
        }

        .multiselect-header:focus-within {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .dropdown-arrow {
            transition: transform 0.3s ease;
            font-size: 12px;
            color: #6b7280;
        }

        .multiselect-header.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .multiselect-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 2px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .multiselect-options.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .multiselect-option {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }

        .multiselect-option:hover {
            background-color: #f8fafc;
        }

        .multiselect-option:last-child {
            border-bottom: none;
        }

        .multiselect-option input[type="checkbox"] {
            display: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
            margin-right: 12px;
            position: relative;
            transition: all 0.2s ease;
        }

        .multiselect-option input[type="checkbox"]:checked + .checkmark {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        .multiselect-option input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .multiselect-options {
                max-height: 150px;
            }
        }
    </style>
    <style>
        /* ===== CORPORATE COLORS V3 ===== */
        :root {
            --primary: #1a4b96;
            --secondary: #2d5aa0;
            --accent: #A9CA48;
            --accent-light: #7BC525;
            --dark: #0f1419;
            --text-primary: #333333;
            --text-secondary: #666666;
        }

        /* ===== ANIMATED BACKGROUND ===== */
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--dark) 0%, var(--primary) 25%, var(--secondary) 50%, var(--primary) 75%, var(--dark) 100%);
            background-size: 400% 400%;
            animation: gradientShift 12s ease infinite;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Geometric Background Elements */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(169, 202, 72, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(45, 90, 160, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(26, 75, 150, 0.03) 0%, transparent 50%);
            z-index: -1;
        }

        /* Floating Geometric Shapes */
        .geometric-shape {
            position: fixed;
            border: 1px solid rgba(169, 202, 72, 0.1);
            border-radius: 4px;
            z-index: -1;
        }

        .shape-1 { width: 60px; height: 60px; top: 10%; left: 10%; animation: float1 8s ease-in-out infinite; }
        .shape-2 { width: 40px; height: 40px; top: 70%; right: 20%; animation: float2 10s ease-in-out infinite; }
        .shape-3 { width: 80px; height: 30px; top: 30%; right: 10%; animation: float3 12s ease-in-out infinite; }

        @keyframes float1 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        @keyframes float2 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(-2deg); }
        }

        @keyframes float3 {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }

        /* ===== MAIN CONTAINER ===== */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .registration-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 800px;
            animation: slideInUp 1s cubic-bezier(0.25, 0.46, 0.45, 0.94), cardFloat 8s ease-in-out infinite;
            transform-style: preserve-3d;
            transition: all 0.3s ease;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cardFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }

        /* ===== HEADER SECTION ===== */
        .registration-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px 30px;
            margin: -40px -40px 30px -40px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .registration-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 6s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .registration-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .registration-subtitle {
            font-size: 16px;
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-weight: 400;
        }

        /* ===== PROGRESS INDICATOR ===== */
        .progress-container {
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(249, 250, 251, 0.8);
            border-radius: 12px;
            border: 1px solid rgba(169, 202, 72, 0.1);
        }

        .progress-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .step-dot.active {
            background: var(--accent);
            color: white;
            border: 3px solid var(--accent);
            box-shadow: 0 4px 12px rgba(169, 202, 72, 0.3);
            transform: scale(1.1);
        }

        .step-dot.inactive {
            background: white;
            color: var(--text-secondary);
            border: 2px solid #e5e7eb;
        }

        .progress-bar {
            flex: 1;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin: 0 20px;
            position: relative;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-light) 100%);
            border-radius: 2px;
            transition: width 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: progressShimmer 2s infinite;
        }

        @keyframes progressShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .progress-labels span.active {
            color: var(--accent);
            font-weight: 600;
        }

        /* ===== STEP SECTIONS ===== */
        .step-section {
            display: none;
            animation: fadeInSlide 0.5s ease-out;
        }

        .step-section.active {
            display: block;
        }

        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .step-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
            text-align: center;
            position: relative;
        }

        .step-title::after {
            content: '';
            width: 60px;
            height: 3px;
            background: var(--accent);
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        /* ===== FORM INPUTS ===== */
        .form-group {
            margin-bottom: 8px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
            letter-spacing: 0.3px;
        }

        .professional-input {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .professional-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 4px 12px rgba(169, 202, 72, 0.15);
            transform: translateY(-1px);
        }

        .professional-input:hover:not(:focus) {
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        .professional-select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23666" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 20px;
        }

        .professional-textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        /* Input Hints */
        .input-hint {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .input-hint strong {
            color: var(--accent);
        }

        /* Error Messages */
        .input-error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 5px;
            padding: 8px 12px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
            animation: errorShake 0.5s ease-out;
        }

        @keyframes errorShake {
            0%, 20%, 40%, 60%, 80%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
        }

        /* ===== BUTTONS ===== */
        .corporate-button {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            color: white;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(169, 202, 72, 0.25);
            position: relative;
            overflow: hidden;
        }

        .corporate-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .corporate-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(169, 202, 72, 0.35);
            background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent) 100%);
        }

        .corporate-button:active::before {
            width: 300px;
            height: 300px;
        }

        .secondary-button {
            background: white;
            color: var(--text-primary);
            border: 2px solid #e5e7eb;
            font-weight: 500;
            padding: 14px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .secondary-button:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* ===== LINKS ===== */
        .corporate-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            padding-bottom: 2px;
        }

        .corporate-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: var(--accent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .corporate-link:hover {
            color: var(--accent);
            transform: translateY(-1px);
        }

        .corporate-link:hover::after {
            width: 100%;
        }

        /* ===== GRID LAYOUT ===== */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-grid .full-width {
                grid-column: 1 / -1;
            }
        }

        /* ===== INFO BOX ===== */
        .info-box {
            background: rgba(169, 202, 72, 0.1);
            border: 1px solid rgba(169, 202, 72, 0.2);
            border-radius: 12px;
            padding: 16px;
            margin: 20px 0;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .info-box strong {
            color: var(--accent);
        }

        /* ===== ACTION BUTTONS CONTAINER ===== */
        .actions-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(169, 202, 72, 0.1);
        }

        /* ===== PARALLAX EFFECT ===== */
        .registration-card {
            transition: transform 0.1s ease-out;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .registration-card {
                padding: 30px 20px;
                margin: 10px;
            }

            .registration-header {
                margin: -30px -20px 10px -20px;
                padding: 20px;
            }

            .registration-title {
                font-size: 24px;
            }

            .step-title {
                font-size: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .actions-container {
                flex-direction: column-reverse;
                gap: 15px;
            }

            .corporate-button,
            .secondary-button {
                width: 100%;
                justify-content: center;
            }

            .progress-steps .step-dot {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .progress-bar {
                margin: 0 15px;
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 10px;
            }

            .registration-card {
                padding: 20px 15px;
            }

            .registration-header {
                margin: -20px -15px 15px -15px;
                padding: 15px;
            }

            .registration-title {
                font-size: 20px;
            }

            .professional-input {
                padding: 14px 16px;
                font-size: 15px;
            }
        }

        /* ===== LOADING STATES ===== */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        // Funcionalidad específica para campos REPSE
        document.addEventListener('DOMContentLoaded', function() {
            const repseYes = document.getElementById('repse_yes');
            const repseNo = document.getElementById('repse_no');
            const repseFields = document.getElementById('repse-fields');
            const otrosCheckbox = document.getElementById('otros_checkbox');
            const otrosInput = document.getElementById('otros-input');

            // Toggle REPSE fields visibility
            function toggleRepseFields() {
                const showFields = repseYes && repseYes.checked;
                repseFields.style.display = showFields ? 'block' : 'none';

                // Update required status for REPSE fields
                const conditionalFields = repseFields.querySelectorAll('[data-conditional-required="repse"]');
                conditionalFields.forEach(field => {
                    field.setAttribute('data-required', showFields ? '1' : '0');
                });

                // Clear REPSE fields when hidden
                if (!showFields) {
                    const repseInputs = repseFields.querySelectorAll('input, select, textarea');
                    repseInputs.forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });
                    // Hide "otros" input as well
                    if (otrosInput) otrosInput.style.display = 'none';
                }
            }

            // Toggle "otros" text input
            function toggleOtrosInput() {
                if (otrosInput && otrosCheckbox) {
                    otrosInput.style.display = otrosCheckbox.checked ? 'block' : 'none';
                    if (!otrosCheckbox.checked) {
                        const otrosDescripcion = document.getElementById('otros_descripcion');
                        if (otrosDescripcion) otrosDescripcion.value = '';
                    }
                }
            }

            // Event listeners
            if (repseYes) repseYes.addEventListener('change', toggleRepseFields);
            if (repseNo) repseNo.addEventListener('change', toggleRepseFields);
            if (otrosCheckbox) otrosCheckbox.addEventListener('change', toggleOtrosInput);

            // Initialize on page load
            toggleRepseFields();
            toggleOtrosInput();

            // REPSE date validation
            const repseExpiryDate = document.getElementById('repse_expiry_date');
            if (repseExpiryDate) {
                repseExpiryDate.addEventListener('blur', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (this.value && selectedDate <= today) {
                        this.setCustomValidity('La fecha de vencimiento debe ser posterior a hoy');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</head>
<body class="font-sans antialiased">
    <!-- Geometric Background Shapes -->
    <div class="geometric-shape shape-1"></div>
    <div class="geometric-shape shape-2"></div>
    <div class="geometric-shape shape-3"></div>

    <div class="auth-container">
        <div class="registration-card">
            <!-- Header -->
            <div class="registration-header">
                <h1 class="registration-title">Registro de Proveedor</h1>
                <p class="registration-subtitle">Complete los datos para crear su cuenta empresarial</p>
            </div>

            <form method="POST" action="{{ route('register') }}" id="supplier-form">
                @csrf

                <!-- Progress Indicator -->
                <div class="progress-container">
                    <div class="progress-steps">
                        <div id="dot-1" class="step-dot active">1</div>
                        <div class="progress-bar">
                            <div id="progress-fill" class="progress-fill" style="width: 0%;"></div>
                        </div>
                        <div id="dot-2" class="step-dot inactive">2</div>
                    </div>
                    <div class="progress-labels">
                        <span id="label-1" class="active">Datos de la Cuenta</span>
                        <span id="label-2">Datos del Proveedor</span>
                    </div>
                </div>

                <!-- Step 1: Account Data -->
                <div data-step="1" class="step-section active">
                    <h2 class="step-title">Datos de la Cuenta</h2>

                    <div class="form-grid">
                        <!-- First Name -->
                        <div class="form-group">
                            <x-input-label for="first_name" :value="__('Nombre(s)')" class="form-label" />
                            <x-text-input id="first_name"
                                        class="professional-input"
                                        type="text"
                                        name="first_name"
                                        :value="old('first_name')"
                                        data-required="1"
                                        autofocus
                                        autocomplete="given-name" />
                            <x-input-error :messages="$errors->get('first_name')" class="input-error" />
                        </div>

                        <!-- Last Name -->
                        <div class="form-group">
                            <x-input-label for="last_name" :value="__('Apellidos')" class="form-label" />
                            <x-text-input id="last_name"
                                        class="professional-input"
                                        type="text"
                                        name="last_name"
                                        :value="old('last_name')"
                                        data-required="1"
                                        autocomplete="family-name" />
                            <x-input-error :messages="$errors->get('last_name')" class="input-error" />
                        </div>

                        <!-- Email -->
                        <div class="form-group full-width">
                            <x-input-label for="email" :value="__('Correo electrónico')" class="form-label" />
                            <x-text-input id="email"
                                        class="professional-input"
                                        type="email"
                                        name="email"
                                        :value="old('email')"
                                        data-required="1"
                                        autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="input-error" />
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <x-input-label for="password" :value="__('Contraseña')" class="form-label" />
                            <x-text-input id="password"
                                        class="professional-input"
                                        type="password"
                                        name="password"
                                        data-required="1"
                                        autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="input-error" />
                        </div>

                        <!-- Password Confirmation -->
                        <div class="form-group">
                            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="form-label" />
                            <x-text-input id="password_confirmation"
                                        class="professional-input"
                                        type="password"
                                        name="password_confirmation"
                                        data-required="1"
                                        autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="input-error" />
                        </div>
                    </div>

                    <div class="actions-container">
                        <a class="corporate-link" href="{{ route('login') }}">
                            {{ __('¿Ya tienes cuenta? Inicia sesión') }}
                        </a>

                        <button type="button" id="next-btn" class="corporate-button">
                            {{ __('Siguiente') }}
                        </button>
                    </div>
                </div>

                <!-- Step 2: Company Data -->
                <div data-step="2" class="step-section">
                    <h2 class="step-title">Datos del Proveedor</h2>

                    <div class="form-grid">
                        <!-- Company Name -->
                        <div class="form-group full-width">
                            <x-input-label for="company_name" :value="__('Razón social / Nombre comercial')" class="form-label" />
                            <x-text-input id="company_name"
                                        class="professional-input"
                                        type="text"
                                        name="company_name"
                                        :value="old('company_name')"
                                        data-required="1" />
                            <x-input-error :messages="$errors->get('company_name')" class="input-error" />
                        </div>

                        <!-- RFC -->
                        <div class="form-group">
                            <x-input-label for="rfc" :value="__('RFC')" class="form-label" />
                            <x-text-input id="rfc"
                                        class="professional-input"
                                        type="text"
                                        name="rfc"
                                        :value="old('rfc')"
                                        data-required="1"
                                        maxlength="13"
                                        style="text-transform: uppercase;"
                                        inputmode="latin"
                                        autocomplete="off" />
                            <div class="input-hint">
                                Formato: <strong>3–4 letras</strong> + <strong>6 dígitos (YYMMDD)</strong> + <strong>3 alfanuméricos</strong>
                            </div>
                            <x-input-error :messages="$errors->get('rfc')" class="input-error" />
                        </div>

                        <!-- Company Phone -->
                        <div class="form-group">
                            <x-input-label for="phone_number" :value="__('Teléfono de la empresa')" class="form-label" />
                            <x-text-input id="phone_number"
                                        class="professional-input"
                                        type="tel"
                                        name="phone_number"
                                        :value="old('phone_number')"
                                        data-required="1"
                                        data-phone="10"
                                        inputmode="numeric"
                                        pattern="\d{10}"
                                        maxlength="10"
                                        minlength="10"
                                        autocomplete="tel" />
                            <div class="input-hint">Formato: <strong>10 dígitos exactos</strong></div>
                            <x-input-error :messages="$errors->get('phone_number')" class="input-error" />
                        </div>

                        <!-- Contact Person -->
                        <div class="form-group">
                            <x-input-label for="contact_person" :value="__('Persona de contacto')" class="form-label" />
                            <x-text-input id="contact_person"
                                        class="professional-input"
                                        type="text"
                                        name="contact_person"
                                        :value="old('contact_person')"
                                        data-required="1" />
                            <x-input-error :messages="$errors->get('contact_person')" class="input-error" />
                        </div>

                        <!-- Contact Phone -->
                        <div class="form-group">
                            <x-input-label for="contact_phone" :value="__('Teléfono de contacto (opcional)')" class="form-label" />
                            <x-text-input id="contact_phone"
                                        class="professional-input"
                                        type="tel"
                                        name="contact_phone"
                                        :value="old('contact_phone')"
                                        data-phone="10"
                                        inputmode="numeric"
                                        pattern="\d{10}"
                                        maxlength="10"
                                        minlength="10"
                                        autocomplete="tel" />
                            <div class="input-hint">Formato: <strong>10 dígitos si se proporciona</strong></div>
                            <x-input-error :messages="$errors->get('contact_phone')" class="input-error" />
                        </div>

                        <!-- Supplier Type -->
                        <div class="form-group">
                            <x-input-label for="supplier_type" :value="__('Tipo de proveedor')" class="form-label" />
                            <select id="supplier_type"
                                    name="supplier_type"
                                    class="professional-input professional-select"
                                    data-required="1">
                                <option value="" disabled {{ old('supplier_type') ? '' : 'selected' }}>Selecciona una opción</option>
                                <option value="product" {{ old('supplier_type') === 'product' ? 'selected' : '' }}>Productos</option>
                                <option value="service" {{ old('supplier_type') === 'service' ? 'selected' : '' }}>Servicios</option>
                                <option value="product_service" {{ old('supplier_type') === 'product_service' ? 'selected' : '' }}>Productos y Servicios</option>
                            </select>
                            <x-input-error :messages="$errors->get('supplier_type')" class="input-error" />
                        </div>

                        <!-- Tax Regime -->
                        <div class="form-group">
                            <x-input-label for="tax_regime" :value="__('Régimen fiscal')" class="form-label" />
                            <select id="tax_regime"
                                    name="tax_regime"
                                    class="professional-input professional-select"
                                    data-required="1">
                                <option value="" disabled {{ old('tax_regime') ? '' : 'selected' }}>Selecciona una opción</option>
                                <option value="individual" {{ old('tax_regime') === 'individual' ? 'selected' : '' }}>Persona Física</option>
                                <option value="corporation" {{ old('tax_regime') === 'corporation' ? 'selected' : '' }}>Persona Moral</option>
                                <option value="resico" {{ old('tax_regime') === 'resico' ? 'selected' : '' }}>RESICO</option>
                            </select>
                            <x-input-error :messages="$errors->get('tax_regime')" class="input-error" />
                        </div>

                        <!-- Company Name -->
                        <div class="form-group full-width">
                            <x-input-label for="economic_activity" :value="__('Actividad económica')" class="form-label" />
                            <x-text-input id="economic_activity"
                                        class="professional-input"
                                        type="text"
                                        name="economic_activity"
                                        :value="old('economic_activity')"
                                        data-required="1" />
                            <div class="input-hint">Formato: <strong>Agregala tal como aparecen en la constancia (Actividad económica)</strong></div>
                            <x-input-error :messages="$errors->get('economic_activity')" class="input-error" />
                        </div>

                        <!-- Default Payment Terms -->
                        <div class="form-group full-width">
                            <x-input-label for="default_payment_terms" :value="__('Condiciones de pago')" class="form-label" />
                            <select id="default_payment_terms"
                                    name="default_payment_terms"
                                    class="professional-input professional-select"
                                    data-required="1">
                                @foreach(\App\Enum\PaymentTerm::options() as $value => $label)
                                    <option value="{{ $value }}" {{ old('default_payment_terms', 'CASH') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="input-hint">Condiciones de pago por defecto para OC y cotizaciones.</div>
                            <x-input-error :messages="$errors->get('default_payment_terms')" class="input-error" />
                        </div>

                        <!-- ===== NUEVOS CAMPOS REPSE ===== -->

                        <!-- REPSE Question -->
                        <div class="form-group full-width">
                            <x-input-label for="provides_specialized_services" :value="__('¿Presta servicios especializados u obras especializadas?')" class="form-label" />
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio"
                                        name="provides_specialized_services"
                                        value="1"
                                        id="repse_yes"
                                        {{ old('provides_specialized_services') === '1' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    Sí
                                </label>
                                <label class="radio-option">
                                    <input type="radio"
                                        name="provides_specialized_services"
                                        value="0"
                                        id="repse_no"
                                        {{ old('provides_specialized_services') === '0' ? 'checked' : '' }}>
                                    <span class="radio-custom"></span>
                                    No
                                </label>
                            </div>
                            <div class="input-hint">
                                Los servicios especializados incluyen: limpieza, vigilancia, mantenimiento, contabilidad, etc.
                            </div>
                            <x-input-error :messages="$errors->get('provides_specialized_services')" class="input-error" />
                        </div>

                        <!-- REPSE Fields Container (initially hidden) -->
                        <div id="repse-fields" class="form-group full-width repse-container" style="display: none;">

                            <!-- REPSE Registration Number -->
                            <div class="form-group">
                                <x-input-label for="repse_registration_number" :value="__('Número de Registro REPSE')" class="form-label" />
                                <x-text-input id="repse_registration_number"
                                            class="professional-input"
                                            type="text"
                                            name="repse_registration_number"
                                            :value="old('repse_registration_number')"
                                            placeholder="Ej: REPSE-123456789"
                                            data-conditional-required="repse" />
                                <div class="input-hint">Formato: REPSE seguido del número asignado</div>
                                <x-input-error :messages="$errors->get('repse_registration_number')" class="input-error" />
                            </div>

                            <!-- REPSE Expiry Date -->
                            <div class="form-group">
                                <x-input-label for="repse_expiry_date" :value="__('Fecha de vencimiento REPSE')" class="form-label" />
                                <x-text-input id="repse_expiry_date"
                                            class="professional-input"
                                            type="date"
                                            name="repse_expiry_date"
                                            :value="old('repse_expiry_date')"
                                            data-conditional-required="repse" />
                                <div class="input-hint">El registro debe estar vigente</div>
                                <x-input-error :messages="$errors->get('repse_expiry_date')" class="input-error" />
                            </div>

                            <!-- Opción 2: Select estilizado personalizado (más moderno) -->
                            <div class="form-group full-width">
                                <x-input-label for="specialized_services_dropdown" :value="__('Tipos de servicios especializados')" class="form-label" />

                                <div class="custom-multiselect" id="custom-multiselect">
                                    <div class="multiselect-header" onclick="toggleDropdown()">
                                        <span id="selected-text">Seleccionar servicios...</span>
                                        <span class="dropdown-arrow">▼</span>
                                    </div>

                                    <div class="multiselect-options" id="multiselect-options">
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="limpieza"
                                                {{ in_array('limpieza', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Servicios de limpieza
                                        </label>
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="vigilancia"
                                                {{ in_array('vigilancia', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Vigilancia y seguridad
                                        </label>
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="mantenimiento"
                                                {{ in_array('mantenimiento', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Mantenimiento
                                        </label>
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="alimentacion"
                                                {{ in_array('alimentacion', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Servicios de alimentación
                                        </label>
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="contabilidad"
                                                {{ in_array('contabilidad', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Servicios contables/administrativos
                                        </label>
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="sistemas"
                                                {{ in_array('sistemas', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Servicios de sistemas/TI
                                        </label>
                                        <label class="multiselect-option">
                                            <input type="checkbox" name="specialized_services_types[]" value="otros" id="otros_checkbox_select"
                                                {{ in_array('otros', old('specialized_services_types', [])) ? 'checked' : '' }}>
                                            <span class="checkmark"></span>
                                            Otros
                                        </label>
                                    </div>
                                </div>

                                <div class="input-hint">
                                    Puede seleccionar múltiples servicios
                                </div>
                                <x-input-error :messages="$errors->get('specialized_services_types')" class="input-error" />
                            </div>

                            <!-- Campo "otros" para la versión personalizada -->
                            <div id="otros-input-custom" class="form-group full-width" style="display: none;">
                                <x-input-label for="otros_descripcion_custom" :value="__('Especifique otros servicios')" class="form-label" />
                                <x-text-input id="otros_descripcion_custom"
                                            class="professional-input"
                                            type="text"
                                            name="otros_descripcion"
                                            :value="old('otros_descripcion')"
                                            placeholder="Describa los otros servicios especializados..." />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="form-group full-width">
                            <x-input-label for="address" :value="__('Domicilio fiscal')" class="form-label" />
                            <textarea id="address"
                                    name="address"
                                    class="professional-input professional-textarea"
                                    rows="3"
                                    data-required="1"
                                    placeholder="Ingrese la dirección completa del domicilio fiscal">{{ old('address') }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="input-error" />
                        </div>
                    </div>

                    <div class="actions-container">
                        <button type="button" id="back-btn" class="secondary-button">
                            {{ __('Atrás') }}
                        </button>

                        <button type="submit" class="corporate-button">
                            {{ __('Registrar Proveedor') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===== PARALLAX EFFECT =====
            const card = document.querySelector('.registration-card');

            document.addEventListener('mousemove', function(e) {
                if (window.innerWidth > 768) {
                    const { clientX, clientY } = e;
                    const { innerWidth, innerHeight } = window;

                    const xRotation = ((clientY / innerHeight) - 0.5) * 3;
                    const yRotation = ((clientX / innerWidth) - 0.5) * -3;

                    card.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg)`;
                }
            });

            document.addEventListener('mouseleave', function() {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg)';
            });

            // ===== FORM STEP MANAGEMENT =====
            let currentStep = 1;
            const totalSteps = 2;

            const nextBtn = document.getElementById('next-btn');
            const backBtn = document.getElementById('back-btn');
            const form = document.getElementById('supplier-form');

            // Progress elements
            const dot1 = document.getElementById('dot-1');
            const dot2 = document.getElementById('dot-2');
            const progressFill = document.getElementById('progress-fill');
            const label1 = document.getElementById('label-1');
            const label2 = document.getElementById('label-2');

            function updateProgress() {
                const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
                progressFill.style.width = progressPercentage + '%';

                // Update dots
                if (currentStep === 1) {
                    dot1.className = 'step-dot active';
                    dot2.className = 'step-dot inactive';
                    label1.className = 'active';
                    label2.className = '';
                } else {
                    dot1.className = 'step-dot inactive';
                    dot2.className = 'step-dot active';
                    label1.className = '';
                    label2.className = 'active';
                }
            }

            function showStep(step) {
                // Hide all steps
                document.querySelectorAll('.step-section').forEach(section => {
                    section.classList.remove('active');
                });

                // Show current step
                document.querySelector(`[data-step="${step}"]`).classList.add('active');
                updateProgress();
            }

            function validateStep(step) {
                const stepSection = document.querySelector(`[data-step="${step}"]`);
                const requiredFields = stepSection.querySelectorAll('[data-required="1"]');
                let isValid = true;

                // Clear previous error states
                requiredFields.forEach(field => {
                    field.style.borderColor = '#e5e7eb';
                    const errorMsg = field.parentNode.querySelector('.validation-error');
                    if (errorMsg) errorMsg.remove();
                });

                requiredFields.forEach(field => {
                    const value = field.value.trim();
                    let fieldValid = true;
                    let errorMessage = '';

                    // Basic required validation
                    if (!value) {
                        fieldValid = false;
                        errorMessage = 'Este campo es obligatorio';
                    } else {
                        // Specific validations
                        if (field.type === 'email') {
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(value)) {
                                fieldValid = false;
                                errorMessage = 'Ingrese un correo electrónico válido';
                            }
                        }

                        if (field.dataset.phone === '10') {
                            const phoneRegex = /^\d{10}$/;
                            if (value && !phoneRegex.test(value)) {
                                fieldValid = false;
                                errorMessage = 'El teléfono debe tener exactamente 10 dígitos';
                            }
                        }

                        if (field.id === 'rfc') {
                            const rfcRegex = /^[A-Z]{3,4}\d{6}[A-Z0-9]{3}$/;
                            if (!rfcRegex.test(value.toUpperCase())) {
                                fieldValid = false;
                                errorMessage = 'RFC inválido. Verifique el formato';
                            }
                        }

                        if (field.id === 'password') {
                            if (value.length < 8) {
                                fieldValid = false;
                                errorMessage = 'La contraseña debe tener al menos 8 caracteres';
                            }
                        }

                        if (field.id === 'password_confirmation') {
                            const password = document.getElementById('password').value;
                            if (value !== password) {
                                fieldValid = false;
                                errorMessage = 'Las contraseñas no coinciden';
                            }
                        }
                    }

                    if (!fieldValid) {
                        isValid = false;
                        field.style.borderColor = '#ef4444';

                        // Create and show error message
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'validation-error input-error';
                        errorDiv.textContent = errorMessage;
                        field.parentNode.appendChild(errorDiv);

                        // Focus first invalid field
                        if (isValid === false && field === requiredFields[0]) {
                            field.focus();
                        }
                    }
                });

                return isValid;
            }

            // Next button handler
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    if (validateStep(currentStep)) {
                        if (currentStep < totalSteps) {
                            currentStep++;
                            showStep(currentStep);
                        }
                    }
                });
            }

            // Back button handler
            if (backBtn) {
                backBtn.addEventListener('click', function() {
                    if (currentStep > 1) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            }

            // Form submission handler
            form.addEventListener('submit', function(e) {
                if (!validateStep(currentStep)) {
                    e.preventDefault();
                    return false;
                }

                // Add loading state to submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // ===== INPUT FORMATTING =====

            // RFC formatting
            const rfcInput = document.getElementById('rfc');
            if (rfcInput) {
                rfcInput.addEventListener('input', function() {
                    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                });
            }

            // Phone number formatting
            document.querySelectorAll('input[data-phone="10"]').forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 10);
                });

                input.addEventListener('keypress', function(e) {
                    if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                        e.preventDefault();
                    }
                });
            });

            // ===== ENHANCED INPUT INTERACTIONS =====

            // Add ripple effect to buttons
            document.querySelectorAll('.corporate-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.5);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);

            // Enhanced focus management
            document.querySelectorAll('.professional-input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentNode.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentNode.classList.remove('focused');
                });
            });

            // Initialize first step
            showStep(currentStep);

            // ===== ERROR HANDLING FOR SERVER VALIDATION =====
            // If there are server-side errors, show the appropriate step
            const errorFields = document.querySelectorAll('.input-error');
            if (errorFields.length > 0) {
                // Find which step has errors
                errorFields.forEach(error => {
                    const stepSection = error.closest('.step-section');
                    if (stepSection) {
                        const stepNumber = parseInt(stepSection.dataset.step);
                        if (stepNumber > currentStep) {
                            currentStep = stepNumber;
                        }
                    }
                });
                showStep(currentStep);
            }

            // ===== ACCESSIBILITY ENHANCEMENTS =====

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.matches('.professional-input')) {
                    e.preventDefault();

                    if (currentStep === 1) {
                        nextBtn.click();
                    } else {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        submitBtn.click();
                    }
                }
            });

            // Auto-focus management
            function focusFirstInput() {
                const currentStepSection = document.querySelector(`[data-step="${currentStep}"]`);
                const firstInput = currentStepSection.querySelector('.professional-input');
                if (firstInput && window.innerWidth > 768) {
                    setTimeout(() => firstInput.focus(), 300);
                }
            }

            // Focus first input on step change
            const originalShowStep = showStep;
            showStep = function(step) {
                originalShowStep(step);
                focusFirstInput();
            };
        });

        (function () {
            const form = document.getElementById('supplier-form');
            const steps = Array.from(document.querySelectorAll('[data-step]'));
            const nextBtn = document.getElementById('next-btn');
            const backBtn = document.getElementById('back-btn');
            const bar = document.getElementById('bar');
            const dot1 = document.getElementById('dot-1');
            const dot2 = document.getElementById('dot-2');

            let current = 1;

            // ====== ENFORCERS (entrada) ======
            const rfcEl = document.getElementById('rfc');
            const RFC_REGEX = /^([A-ZÑ&]{3,4})\d{6}[A-Z0-9]{3}$/;
            if (rfcEl) {
                rfcEl.addEventListener('keydown', (e) => {
                    if (e.key === ' ') e.preventDefault();
                });
                rfcEl.addEventListener('input', (e) => {
                    let v = e.target.value.toUpperCase();
                    v = v.replace(/\s+/g, '').replace(/[^A-Z0-9Ñ&]/g, '');
                    e.target.value = v;
                    e.target.setCustomValidity('');
                });
                rfcEl.addEventListener('blur', () => {
                    const v = rfcEl.value.trim();
                    if (v.length > 0 && !RFC_REGEX.test(v)) {
                        rfcEl.setCustomValidity('RFC inválido. Formato esperado: 3–4 letras + 6 dígitos (YYMMDD) + 3 alfanuméricos.');
                    } else {
                        rfcEl.setCustomValidity('');
                    }
                });
            }

            // Enforce teléfonos de 10 dígitos exactos
            const phoneInputs = Array.from(document.querySelectorAll('[data-phone="10"]'));
            function sanitizePhone(el) {
                let v = el.value.replace(/\D/g, '');
                if (v.length > 10) v = v.slice(0, 10);
                el.value = v;
            }
            phoneInputs.forEach((el) => {
                el.addEventListener('keydown', (e) => {
                    if (e.key === ' ') e.preventDefault();
                });
                el.addEventListener('input', () => {
                    sanitizePhone(el);
                    el.setCustomValidity('');
                });
                el.addEventListener('blur', () => {
                    const v = el.value;
                    if (v.length === 0 && el.id === 'contact_phone') {
                        el.setCustomValidity('');
                        return;
                    }
                    if (!/^\d{10}$/.test(v)) {
                        el.setCustomValidity('Debe tener exactamente 10 dígitos (sin espacios ni letras).');
                    } else {
                        el.setCustomValidity('');
                    }
                });
            });

            // ====== NUEVA FUNCIONALIDAD REPSE ======

            // Manejo de campos REPSE condicionales
            function updateConditionalRequired() {
                const repseYes = document.getElementById('repse_yes');
                const repseRequired = repseYes && repseYes.checked;

                // Actualizar campos condicionales REPSE
                document.querySelectorAll('[data-conditional-required="repse"]').forEach(field => {
                    field.required = repseRequired;
                    if (repseRequired) {
                        field.setAttribute('data-required', '1');
                    } else {
                        field.removeAttribute('data-required');
                        field.required = false;
                    }
                });

                // Validar que al menos un servicio esté seleccionado si REPSE es requerido
                const serviceCheckboxes = document.querySelectorAll('input[name="specialized_services_types[]"]');
                const atLeastOneChecked = Array.from(serviceCheckboxes).some(cb => cb.checked);

                if (repseRequired && !atLeastOneChecked) {
                    // Marcar el primer checkbox como required para mostrar error
                    if (serviceCheckboxes.length > 0) {
                        serviceCheckboxes[0].setCustomValidity('Debe seleccionar al menos un tipo de servicio especializado');
                    }
                } else {
                    serviceCheckboxes.forEach(cb => cb.setCustomValidity(''));
                }
            }

            // Event listeners para REPSE
            const repseRadios = document.querySelectorAll('input[name="provides_specialized_services"]');
            repseRadios.forEach(radio => {
                radio.addEventListener('change', updateConditionalRequired);
            });

            // Event listeners para checkboxes de servicios especializados
            const serviceCheckboxes = document.querySelectorAll('input[name="specialized_services_types[]"]');
            serviceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateConditionalRequired);
            });

            // Validación de fecha REPSE
            const repseExpiryDate = document.getElementById('repse_expiry_date');
            if (repseExpiryDate) {
                repseExpiryDate.addEventListener('blur', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (this.value && selectedDate <= today) {
                        this.setCustomValidity('La fecha de vencimiento debe ser posterior a hoy');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Validación de formato número REPSE
            const repseNumber = document.getElementById('repse_registration_number');
            if (repseNumber) {
                repseNumber.addEventListener('input', function() {
                    let value = this.value.toUpperCase();
                    // Permitir formato libre pero sugerir formato REPSE-
                    this.value = value;
                });

                repseNumber.addEventListener('blur', function() {
                    const value = this.value.trim();
                    if (value && !value.startsWith('REPSE-') && value.length > 0) {
                        // Advertencia suave, no error estricto
                        this.setCustomValidity('');
                        // Podrías mostrar una advertencia visual aquí si quieres
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // ====== Lógica de pasos / required dinámico ======
            function syncRequired() {
                // Desactiva required de TODOS los campos
                form.querySelectorAll('[data-required], [required]').forEach(el => el.required = false);

                // Activa required solo en el paso visible
                const currentStepEl = steps.find(s => Number(s.dataset.step) === current);
                currentStepEl.querySelectorAll('[data-required]').forEach(el => el.required = true);

                // Actualizar required condicionales
                updateConditionalRequired();
            }

            function showStep(step) {
                current = step;
                steps.forEach(s => s.classList.toggle('hidden', Number(s.dataset.step) !== step));
                if (step === 1) {
                    bar.style.width = '0%';
                    dot1.className = 'w-8 h-8 flex items-center justify-center rounded-full border border-indigo-600 text-white bg-indigo-600';
                    dot2.className = 'w-8 h-8 flex items-center justify-center rounded-full border border-gray-300 text-gray-500';
                } else {
                    bar.style.width = '100%';
                    dot1.className = 'w-8 h-8 flex items-center justify-center rounded-full border border-indigo-600 text-indigo-600 bg-white';
                    dot2.className = 'w-8 h-8 flex items-center justify-center rounded-full border border-indigo-600 text-white bg-indigo-600';
                }
                syncRequired();
                const firstInput = steps.find(s => Number(s.dataset.step) === step).querySelector('input, select, textarea');
                if (firstInput) firstInput.focus();
            }

            // Validar campos visibles del paso actual
            function validateCurrentStep() {
                let valid = true;
                const currentStepEl = steps.find(s => Number(s.dataset.step) === current);

                // Dispara eventos para validaciones personalizadas
                currentStepEl.querySelectorAll('input, select, textarea').forEach(el => {
                    el.dispatchEvent(new Event('blur'));
                });

                // Validación especial para REPSE si está en el paso actual
                const repseYes = document.getElementById('repse_yes');
                if (repseYes && repseYes.checked && currentStepEl.contains(repseYes)) {
                    // Verificar que al menos un servicio esté seleccionado
                    const serviceCheckboxes = currentStepEl.querySelectorAll('input[name="specialized_services_types[]"]');
                    const atLeastOneChecked = Array.from(serviceCheckboxes).some(cb => cb.checked);

                    if (!atLeastOneChecked) {
                        valid = false;
                        // Mostrar error en el primer checkbox
                        if (serviceCheckboxes.length > 0) {
                            serviceCheckboxes[0].setCustomValidity('Debe seleccionar al menos un tipo de servicio especializado');
                            serviceCheckboxes[0].reportValidity();
                        }
                    }
                }

                // Chequeo estándar de validación HTML5
                const fields = Array.from(currentStepEl.querySelectorAll('[data-required], [required], [data-phone="10"], #rfc'));
                for (const el of fields) {
                    if (!el.checkValidity()) {
                        el.reportValidity();
                        valid = false;
                        break;
                    }
                }
                return valid;
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    if (!validateCurrentStep()) return;
                    showStep(2);
                });
            }
            if (backBtn) {
                backBtn.addEventListener('click', function () {
                    showStep(1);
                });
            }

            // Validación final al enviar el formulario
            form.addEventListener('submit', function (e) {
                // Valida paso actual
                if (!validateCurrentStep()) {
                    e.preventDefault();
                    return;
                }

                // Valida TODOS los pasos
                let allValid = true;
                steps.forEach(s => {
                    s.querySelectorAll('input, select, textarea').forEach(el => el.dispatchEvent(new Event('blur')));
                    s.querySelectorAll('[data-required], [required], [data-phone="10"], #rfc').forEach(el => {
                        if (!el.checkValidity()) allValid = false;
                    });
                });

                // Validación final específica para REPSE en todos los pasos
                const repseYes = document.getElementById('repse_yes');
                if (repseYes && repseYes.checked) {
                    const serviceCheckboxes = document.querySelectorAll('input[name="specialized_services_types[]"]');
                    const atLeastOneChecked = Array.from(serviceCheckboxes).some(cb => cb.checked);

                    if (!atLeastOneChecked) {
                        allValid = false;
                        if (serviceCheckboxes.length > 0) {
                            serviceCheckboxes[0].setCustomValidity('Debe seleccionar al menos un tipo de servicio especializado');
                        }
                    }
                }

                if (!allValid) {
                    e.preventDefault();
                    // Busca el primer campo inválido y salta al paso correspondiente
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        const stepEl = firstInvalid.closest('[data-step]');
                        if (stepEl) showStep(Number(stepEl.dataset.step));
                        firstInvalid.reportValidity();
                    }
                }
            });

            // RFC a mayúsculas en tiempo real
            if (rfcEl) {
                rfcEl.addEventListener('input', () => rfcEl.value = rfcEl.value.toUpperCase());
            }

            // Inicialización
            showStep(1);

            // Inicializar estado REPSE si hay datos previos (para casos de error de validación del servidor)
            updateConditionalRequired();
        })();

        // ====== FUNCIONALIDAD ADICIONAL PARA UX MEJORADA ======
        document.addEventListener('DOMContentLoaded', function() {

            // ===== ENHANCED INPUT INTERACTIONS =====

            // Add ripple effect to buttons
            document.querySelectorAll('.corporate-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.5);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
                .corporate-button {
                    position: relative;
                    overflow: hidden;
                }
            `;
            document.head.appendChild(style);

            // Enhanced focus management
            document.querySelectorAll('.professional-input').forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentNode.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentNode.classList.remove('focused');
                });
            });

            // ===== REPSE SPECIFIC UX ENHANCEMENTS =====

            // Animación suave para mostrar/ocultar campos REPSE
            const repseFields = document.getElementById('repse-fields');
            const repseRadios = document.querySelectorAll('input[name="provides_specialized_services"]');

            repseRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const showFields = document.getElementById('repse_yes').checked;

                    if (showFields) {
                        repseFields.style.display = 'block';
                        repseFields.style.opacity = '0';
                        repseFields.style.transform = 'translateY(-10px)';

                        // Animación de entrada
                        setTimeout(() => {
                            repseFields.style.transition = 'all 0.3s ease';
                            repseFields.style.opacity = '1';
                            repseFields.style.transform = 'translateY(0)';
                        }, 10);
                    } else {
                        repseFields.style.transition = 'all 0.3s ease';
                        repseFields.style.opacity = '0';
                        repseFields.style.transform = 'translateY(-10px)';

                        setTimeout(() => {
                            repseFields.style.display = 'none';
                        }, 300);
                    }
                });
            });

            // Mejorar la experiencia de selección de checkboxes
            const serviceCheckboxes = document.querySelectorAll('input[name="specialized_services_types[]"]');
            serviceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Limpiar errores cuando se selecciona al menos uno
                    const atLeastOneChecked = Array.from(serviceCheckboxes).some(cb => cb.checked);
                    if (atLeastOneChecked) {
                        serviceCheckboxes.forEach(cb => cb.setCustomValidity(''));
                    }

                    // Manejar campo "otros"
                    if (this.value === 'otros') {
                        const otrosInput = document.getElementById('otros-input');
                        const otrosDescripcion = document.getElementById('otros_descripcion');

                        if (this.checked) {
                            otrosInput.style.display = 'block';
                            otrosInput.style.opacity = '0';
                            setTimeout(() => {
                                otrosInput.style.transition = 'opacity 0.3s ease';
                                otrosInput.style.opacity = '1';
                                otrosDescripcion.focus();
                            }, 10);
                        } else {
                            otrosInput.style.transition = 'opacity 0.3s ease';
                            otrosInput.style.opacity = '0';
                            setTimeout(() => {
                                otrosInput.style.display = 'none';
                                otrosDescripcion.value = '';
                            }, 300);
                        }
                    }
                });
            });

            // Validación mejorada para el número REPSE
            const repseNumber = document.getElementById('repse_registration_number');
            if (repseNumber) {
                repseNumber.addEventListener('input', function() {
                    let value = this.value.toUpperCase();

                    // Auto-completar formato si el usuario no lo incluye
                    if (value.length > 0 && !value.startsWith('REPSE-') && /^\d/.test(value)) {
                        value = 'REPSE-' + value.replace(/[^0-9]/g, '');
                    }

                    this.value = value;
                });
            }

            // Validación de fecha mejorada con indicador visual
            const repseExpiryDate = document.getElementById('repse_expiry_date');
            if (repseExpiryDate) {
                repseExpiryDate.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    const threMonthsFromNow = new Date();
                    threMonthsFromNow.setMonth(today.getMonth() + 3);

                    // Limpiar clases previas
                    this.classList.remove('date-warning', 'date-error');

                    if (selectedDate <= today) {
                        this.classList.add('date-error');
                        this.setCustomValidity('La fecha de vencimiento debe ser posterior a hoy');
                    } else if (selectedDate <= threMonthsFromNow) {
                        this.classList.add('date-warning');
                        this.setCustomValidity('');
                        // Mostrar advertencia visual (no bloquea el envío)
                        this.title = 'Advertencia: El registro vence en menos de 3 meses';
                    } else {
                        this.setCustomValidity('');
                        this.title = '';
                    }
                });
            }

            // Agregar estilos para los estados de fecha
            const dateStyles = document.createElement('style');
            dateStyles.textContent = `
                .date-warning {
                    border-color: #f59e0b !important;
                    background-color: #fef3c7;
                }
                .date-error {
                    border-color: #ef4444 !important;
                    background-color: #fee2e2;
                }
            `;
            document.head.appendChild(dateStyles);

            // ===== ACCESSIBILITY ENHANCEMENTS =====

            // Keyboard navigation mejorada
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.matches('.professional-input')) {
                    e.preventDefault();

                    const currentStep = document.querySelector('.step-section:not(.hidden)');
                    const currentStepNumber = parseInt(currentStep.dataset.step);

                    if (currentStepNumber === 1) {
                        const nextBtn = document.getElementById('next-btn');
                        if (nextBtn) nextBtn.click();
                    } else {
                        const submitBtn = document.querySelector('button[type="submit"]');
                        if (submitBtn) submitBtn.click();
                    }
                }
            });

            // Manejar estado inicial si hay errores del servidor
            const errorFields = document.querySelectorAll('.input-error');
            if (errorFields.length > 0) {
                // Encontrar qué paso tiene errores y mostrarlo
                errorFields.forEach(error => {
                    const stepSection = error.closest('.step-section');
                    if (stepSection) {
                        const stepNumber = parseInt(stepSection.dataset.step);
                        // Este manejo ya está en la función principal, pero asegurar
                        // que los campos REPSE estén visibles si hay errores relacionados
                        const repseError = error.closest('#repse-fields');
                        if (repseError) {
                            const repseYes = document.getElementById('repse_yes');
                            if (repseYes) repseYes.checked = true;
                            repseFields.style.display = 'block';
                        }
                    }
                });
            }
        });
    </script>
    <script>
        // JavaScript para el multiselect personalizado
        function toggleDropdown() {
            const header = document.querySelector('.multiselect-header');
            const options = document.getElementById('multiselect-options');

            header.classList.toggle('active');
            options.classList.toggle('show');
        }

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', function(e) {
            const multiselect = document.getElementById('custom-multiselect');
            if (!multiselect.contains(e.target)) {
                const header = document.querySelector('.multiselect-header');
                const options = document.getElementById('multiselect-options');
                header.classList.remove('active');
                options.classList.remove('show');
            }
        });

        // Actualizar texto del header cuando se seleccionan opciones
        function updateSelectedText() {
            const checkboxes = document.querySelectorAll('#custom-multiselect input[type="checkbox"]');
            const selectedText = document.getElementById('selected-text');
            const checked = Array.from(checkboxes).filter(cb => cb.checked);

            if (checked.length === 0) {
                selectedText.textContent = 'Seleccionar servicios...';
                selectedText.style.color = '#9ca3af';
            } else if (checked.length === 1) {
                const label = checked[0].parentElement.textContent.trim();
                selectedText.textContent = label;
                selectedText.style.color = '#111827';
            } else {
                selectedText.textContent = `${checked.length} servicios seleccionados`;
                selectedText.style.color = '#111827';
            }
        }

        // Event listeners para actualizar el texto
        document.addEventListener('DOMContentLoaded', function() {
            const customCheckboxes = document.querySelectorAll('#custom-multiselect input[type="checkbox"]');
            const selectMultiple = document.getElementById('specialized_services_types');

            // Para multiselect personalizado
            customCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedText();

                    // Manejar campo "otros"
                    if (this.value === 'otros') {
                        const otrosInput = document.getElementById('otros-input-custom');
                        otrosInput.style.display = this.checked ? 'block' : 'none';
                    }
                });
            });

            // Para select múltiple tradicional
            if (selectMultiple) {
                selectMultiple.addEventListener('change', function() {
                    const selectedValues = Array.from(this.selectedOptions).map(option => option.value);
                    const otrosInput = document.getElementById('otros-input-select');
                    otrosInput.style.display = selectedValues.includes('otros') ? 'block' : 'none';
                });
            }

            // Inicializar estado
            updateSelectedText();

            // Verificar si "otros" está seleccionado inicialmente
            const otrosChecked = document.querySelector('input[value="otros"]:checked');
            if (otrosChecked) {
                document.getElementById('otros-input-custom').style.display = 'block';
            }
        });
    </script>
</body>
</html>
