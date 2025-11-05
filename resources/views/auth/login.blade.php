<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Proveedores - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', Arial, sans-serif;
        }

        body, html {
            height: 100%;
            overflow-x: hidden;
        }

        /* Fondo animado principal */
        .dark-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(-45deg, #0a0a0a, #1a1a2e, #16213e, #0f1419);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -3;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Capa de partículas animadas */
        .particles-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            opacity: 0.7;
        }

        .floating-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(169, 202, 72, 0.6);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(169, 202, 72, 0.3);
        }

        .floating-particle:nth-child(1) {
            left: 10%;
            animation: floatUp 12s infinite linear;
            animation-delay: 0s;
        }
        .floating-particle:nth-child(2) {
            left: 20%;
            animation: floatUp 16s infinite linear;
            animation-delay: 2s;
            width: 6px;
            height: 6px;
        }
        .floating-particle:nth-child(3) {
            left: 30%;
            animation: floatUp 14s infinite linear;
            animation-delay: 4s;
        }
        .floating-particle:nth-child(4) {
            left: 40%;
            animation: floatUp 18s infinite linear;
            animation-delay: 6s;
            width: 8px;
            height: 8px;
        }
        .floating-particle:nth-child(5) {
            left: 50%;
            animation: floatUp 20s infinite linear;
            animation-delay: 8s;
        }
        .floating-particle:nth-child(6) {
            left: 60%;
            animation: floatUp 15s infinite linear;
            animation-delay: 10s;
            width: 5px;
            height: 5px;
        }
        .floating-particle:nth-child(7) {
            left: 70%;
            animation: floatUp 13s infinite linear;
            animation-delay: 1s;
        }
        .floating-particle:nth-child(8) {
            left: 80%;
            animation: floatUp 17s infinite linear;
            animation-delay: 3s;
            width: 7px;
            height: 7px;
        }
        .floating-particle:nth-child(9) {
            left: 90%;
            animation: floatUp 19s infinite linear;
            animation-delay: 5s;
        }
        .floating-particle:nth-child(10) {
            left: 85%;
            animation: floatUp 11s infinite linear;
            animation-delay: 7s;
        }

        @keyframes floatUp {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Contenedor principal con parallax */
        .main-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .header {
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 32px;
            font-weight: 600;
            text-shadow: 0 0 20px rgba(169, 202, 72, 0.5);
            animation: titlePulse 3s ease-in-out infinite;
        }

        @keyframes titlePulse {
            0%, 100% {
                text-shadow: 0 0 20px rgba(169, 202, 72, 0.5);
            }
            50% {
                text-shadow: 0 0 30px rgba(169, 202, 72, 0.8), 0 0 40px rgba(169, 202, 72, 0.3);
            }
        }

        /* Container del login con glassmorphism */
        .login-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            width: 100%;
            max-width: 900px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .logo-section {
            flex: 1;
            background: rgba(255, 255, 255, 0.95);
            padding: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .logo-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(transparent, rgba(169, 202, 72, 0.1), transparent);
            animation: logoRotate 10s linear infinite;
        }

        @keyframes logoRotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo-section img {
            max-width: 85%;
            height: auto;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
        }

        .form-section {
            flex: 1;
            background: linear-gradient(135deg, rgba(26, 75, 150, 0.9), rgba(45, 90, 160, 0.9));
            backdrop-filter: blur(20px);
            padding: 50px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(169, 202, 72, 0.2), transparent);
            z-index: -1;
        }

        @keyframes borderFlow {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .welcome-text {
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            color: #A9CA48;
            margin-bottom: 10px;
            font-size: 28px;
            animation: welcomeGlow 2s ease-in-out infinite alternate;
        }

        @keyframes welcomeGlow {
            from {
                text-shadow: 0 0 10px rgba(169, 202, 72, 0.5);
            }
            to {
                text-shadow: 0 0 20px rgba(169, 202, 72, 0.8);
            }
        }

        .form-group {
            margin-bottom: 25px;
        }

        .modern-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(169, 202, 72, 0.3);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .modern-input:focus {
            border-color: #A9CA48;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 25px rgba(169, 202, 72, 0.4);
            transform: translateY(-2px);
        }

        .modern-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .remember-section {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            gap: 10px;
        }

        .modern-checkbox {
            width: 20px;
            height: 20px;
            border: 2px solid #A9CA48;
            border-radius: 4px;
            background: transparent;
            cursor: pointer;
            position: relative;
        }

        .modern-checkbox:checked::after {
            content: '✓';
            position: absolute;
            top: -2px;
            left: 2px;
            color: #A9CA48;
            font-weight: bold;
        }

        .button-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .register-link {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .register-link:hover {
            color: #A9CA48;
            text-shadow: 0 0 10px rgba(169, 202, 72, 0.5);
        }

        .login-button {
            background: linear-gradient(45deg, #A9CA48, #7BC525);
            border: none;
            padding: 15px 35px;
            border-radius: 12px;
            color: #1a4b96;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(169, 202, 72, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(169, 202, 72, 0.5);
        }

        .login-button:active {
            transform: translateY(-1px);
        }

        .forgot-password {
            color: #A9CA48;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            text-shadow: 0 0 10px rgba(169, 202, 72, 0.7);
        }

        .error-message {
            background: rgba(255, 107, 107, 0.2);
            border: 1px solid rgba(255, 107, 107, 0.5);
            color: #ff6b6b;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 14px;
        }

        .success-message {
            background: rgba(169, 202, 72, 0.2);
            border: 1px solid rgba(169, 202, 72, 0.5);
            color: #A9CA48;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 100%;
            }

            .logo-section, .form-section {
                padding: 30px;
            }

            .header h1 {
                font-size: 24px;
            }

            .button-section {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Fondo oscuro animado -->
    <div class="dark-background"></div>

    <!-- Partículas flotantes -->
    <div class="particles-layer">
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
        <div class="floating-particle"></div>
    </div>

    <div class="main-container" id="mainContainer">
        <div class="header">
            <h1>Portal de Proveedores</h1>
        </div>

        <div class="login-container" id="loginContainer">
            <div class="logo-section">
                <img src="{{ asset('images/logos/logo_TotalGas_ver.png') }}" alt="TotalGas Logo">
            </div>

            <div class="form-section">
                <div class="welcome-text">
                    <h2>¡Bienvenid@!</h2>
                    <p>Ingresa tus datos para continuar</p>
                </div>

                <!-- Mensajes de estado -->
                @if (session('status'))
                    <div class="success-message">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Campo Email -->
                    <div class="form-group">
                        <input type="email"
                               class="modern-input @error('email') error-border @enderror"
                               name="email"
                               id="email"
                               placeholder="Correo electrónico o Usuario"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               autocomplete="username" />
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-group">
                        <input type="password"
                               class="modern-input @error('password') error-border @enderror"
                               name="password"
                               id="password"
                               placeholder="Contraseña"
                               required
                               autocomplete="current-password" />
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Checkbox Recuérdame -->
                    <div class="remember-section">
                        <input type="checkbox"
                               name="remember"
                               id="remember_me"
                               class="modern-checkbox" />
                        <label for="remember_me">Recuérdame</label>
                    </div>

                    <!-- Botones -->
                    <div class="button-section">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="register-link">
                                ¿No tienes cuenta? Regístrate
                            </a>
                        @endif

                        <button type="submit" class="login-button" id="loginBtn">
                            Entrar
                        </button>
                    </div>
                </form>

                <!-- Contraseña olvidada -->
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-password">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif

                <!-- Errores generales -->
                @if ($errors->any())
                    <div style="margin-top: 20px;">
                        @foreach ($errors->all() as $error)
                            <div class="error-message">
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Efecto parallax con el mouse
        document.addEventListener('mousemove', function(e) {
            const mouseX = (e.clientX / window.innerWidth) - 0.5;
            const mouseY = (e.clientY / window.innerHeight) - 0.5;

            const container = document.getElementById('loginContainer');
            const header = document.querySelector('.header');

            // Efecto parallax en el contenedor principal
            container.style.transform = `perspective(1000px) rotateY(${mouseX * 5}deg) rotateX(${mouseY * -5}deg) translateZ(0)`;

            // Efecto parallax en el header
            header.style.transform = `translateX(${mouseX * 20}px) translateY(${mouseY * 20}px)`;
        });

        // Animación de entrada
        window.addEventListener('load', function() {
            const container = document.getElementById('loginContainer');
            const header = document.querySelector('.header');

            // Animación de entrada del header
            header.style.opacity = '0';
            header.style.transform = 'translateY(-50px)';

            // Animación de entrada del container
            container.style.opacity = '0';
            container.style.transform = 'translateY(100px) scale(0.9)';

            setTimeout(() => {
                header.style.transition = 'all 1s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                container.style.transition = 'all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';

                header.style.opacity = '1';
                header.style.transform = 'translateY(0px)';

                container.style.opacity = '1';
                container.style.transform = 'translateY(0px) scale(1)';
            }, 300);
        });

        // Efecto de ondas en el botón
        document.getElementById('loginBtn').addEventListener('click', function(e) {
            const button = this;
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;

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

            button.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
                style.remove();
            }, 600);
        });

        // Efecto de focus en inputs
        document.querySelectorAll('.modern-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });

            input.addEventListener('blur', function() {
                this.style.transform = 'translateY(0px) scale(1)';
            });
        });
    </script>
</body>
</html>
