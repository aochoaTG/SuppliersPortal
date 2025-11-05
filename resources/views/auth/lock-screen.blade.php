{{-- resources/views/auth/lock-screen.blade.php --}}
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pantalla de bloqueo - {{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #5b5bf6;
            --primary-light: rgba(99, 102, 241, 0.1);
            --surface: #0f0f0f;
            --surface-light: #1a1a1a;
            --surface-lighter: #2a2a2a;
            --text: #ffffff;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --border: #27272a;
            --error: #ef4444;
            --success: #10b981;
            --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(99, 102, 241, 0.02) 0%, transparent 50%);
            pointer-events: none;
        }

        .lock-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .lock-card {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        .lock-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            opacity: 0.5;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .lock-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .avatar-container {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .avatar-container::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--primary-hover));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .avatar-container:hover::after {
            opacity: 1;
        }

        .lock-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .lock-subtitle {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .lock-description {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .lock-form {
            margin-bottom: 24px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--surface);
            color: var(--text);
            font-size: 16px;
            font-weight: 500;
            outline: none;
            transition: all 0.2s ease;
            position: relative;
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            background: var(--surface-light);
        }

        .form-input:focus + .input-icon {
            color: var(--primary);
        }

        .error-message {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--error);
            font-size: 14px;
            font-weight: 500;
            margin-top: 8px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .unlock-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .unlock-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .unlock-btn:hover::before {
            left: 100%;
        }

        .unlock-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }

        .unlock-btn:active {
            transform: translateY(0);
        }

        .unlock-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .lock-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .footer-text {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 8px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            color: var(--text);
            background: var(--surface-light);
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .hidden {
            display: none;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .lock-card {
                padding: 32px 24px;
                border-radius: 20px;
            }

            .lock-title {
                font-size: 24px;
            }

            .avatar {
                width: 70px;
                height: 70px;
            }
        }

        /* Success animation */
        @keyframes success {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .success {
            animation: success 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="lock-container">
        <div class="lock-card">
            <div class="lock-header">
                @php
                    $avatar = method_exists($user, 'getAttribute') && $user->avatar
                        ? asset('storage/'.$user->avatar)
                        : asset('images/users/avatar-1.jpg');
                @endphp

                <div class="avatar-container">
                    <img class="avatar" src="{{ $avatar }}" alt="Avatar de {{ $user->name ?? $user->email }}">
                </div>

                <h1 class="lock-title">Sesión Bloqueada</h1>
                <div class="lock-subtitle">{{ $user->name ?? $user->email }}</div>
                <div class="lock-description">Ingresa tu contraseña para continuar con tu sesión</div>
            </div>

            <form method="POST" action="{{ route('lockscreen.unlock') }}" class="lock-form" id="unlockForm">
                @csrf
                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        autofocus
                        required
                        id="passwordInput"
                    >
                    <i data-lucide="lock" class="input-icon"></i>

                    @error('password')
                        <div class="error-message">
                            <i data-lucide="alert-circle" size="16"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="unlock-btn" id="unlockButton">
                    <span class="btn-text">Desbloquear Sesión</span>
                    <i data-lucide="unlock" class="btn-icon"></i>
                    <div class="loading-spinner hidden"></div>
                </button>
            </form>

            <div class="lock-footer">
                <div class="footer-text">¿No eres {{ $user->name ?? 'tú' }}?</div>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i data-lucide="log-out" size="14"></i>
                        Iniciar sesión con otra cuenta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Form submission handling
        document.getElementById('unlockForm').addEventListener('submit', function(e) {
            const button = document.getElementById('unlockButton');
            const btnText = button.querySelector('.btn-text');
            const btnIcon = button.querySelector('.btn-icon');
            const spinner = button.querySelector('.loading-spinner');

            // Show loading state
            button.disabled = true;
            btnText.textContent = 'Verificando...';
            btnIcon.classList.add('hidden');
            spinner.classList.remove('hidden');
        });

        // Add shake animation on error
        @if($errors->has('password'))
            document.addEventListener('DOMContentLoaded', function() {
                const inputGroup = document.querySelector('.input-group');
                inputGroup.style.animation = 'shake 0.4s ease-in-out';

                setTimeout(() => {
                    inputGroup.style.animation = '';
                }, 400);
            });
        @endif

        // Auto-focus on password input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('passwordInput');
            if (passwordInput) {
                passwordInput.focus();
            }
        });

        // Add enter key support
        document.getElementById('passwordInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('unlockForm').dispatchEvent(new Event('submit'));
            }
        });

        // Add subtle parallax effect to card
        document.addEventListener('mousemove', function(e) {
            const card = document.querySelector('.lock-card');
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Check if mouse is within card bounds
            if (x >= 0 && x <= rect.width && y >= 0 && y <= rect.height) {
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                // Much more subtle rotation (increased divisor from 20 to 60)
                const rotateX = (y - centerY) / 40;
                const rotateY = (centerX - x) / 40;

                // Add smooth transition
                card.style.transition = 'transform 0.1s ease-out';
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(5px)`;
            }
        });

        // Smooth reset when mouse leaves the card
        document.querySelector('.lock-card').addEventListener('mouseleave', function() {
            const card = this;
            card.style.transition = 'transform 0.3s ease-out';
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
        });
    </script>
</body>
</html>
