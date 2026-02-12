{{-- resources/views/auth/forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <title>Recuperar contraseña — Portal de Proveedores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    {{-- Tipografía corporativa --}}
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root{
            --color-primary:#1a4b96;
            --color-secondary:#2d5aa0;
            --color-accent:#A9CA48;
            --color-accent-2:#7BC525;
            --color-dark:#0f1419;
            --color-text:#333333;
            --color-text-2:#666666;
            --radius-xl:20px;
        }

        /* Reset mínimo + tipografía */
        *{ box-sizing:border-box; }
        html,body{ height:100%; }
        body{
            margin:0;
            font-family:Poppins, Arial, sans-serif;
            color:var(--color-text);
            background: linear-gradient(135deg, var(--color-dark) 0%, var(--color-primary) 25%, var(--color-secondary) 50%, var(--color-primary) 75%, var(--color-dark) 100%);
            overflow-x:hidden;
        }

        /* Fondo animado muy sutil */
        .bg-animated{
            position:fixed; inset:0;
            background-size:200% 200%;
            animation:bgShift 12s ease-in-out infinite;
            z-index:-3;
        }
        @keyframes bgShift{
            0%{ background-position:0% 50%; }
            50%{ background-position:100% 50%; }
            100%{ background-position:0% 50%; }
        }

        /* Elementos geométricos discretos */
        .bg-geo{
            position:fixed; inset:0;
            pointer-events:none;
            z-index:-2;
        }
        .geo{
            position:absolute;
            border:1px solid rgba(255,255,255,0.08);
            border-radius:12px;
            transform:rotate(0.5deg);
            backdrop-filter: blur(2px);
        }
        .geo.g1{ width:160px; height:100px; top:10%; left:8%; }
        .geo.g2{ width:120px; height:120px; top:70%; left:15%; }
        .geo.g3{ width:220px; height:140px; top:20%; right:12%; }
        .geo.g4{ width:100px; height:160px; bottom:10%; right:6%; }

        /* Layout principal */
        .page{
            min-height:100dvh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
            perspective: 1200px; /* para parallax/tilt sutil */
        }

        /* Tarjeta glassmorphism */
        .card{
            width:min(960px, 100%);
            background:rgba(255,255,255,0.95);
            border-radius:var(--radius-xl);
            box-shadow:0 20px 60px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px);
            overflow:hidden;
            transform-style:preserve-3d;
            animation:floaty 8s ease-in-out infinite;
        }
        @keyframes floaty{
            0%{ transform:translateY(0); }
            50%{ transform:translateY(-6px); }
            100%{ transform:translateY(0); }
        }

        /* Header con gradiente + shimmer */
        .card__header{
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            color:#fff;
            padding:28px 28px;
            position:relative;
        }
        .card__title{
            margin:0;
            font-size:clamp(18px, 2vw, 22px);
            font-weight:600;
            text-shadow:0 1px 2px rgba(0,0,0,0.25);
        }
        .shimmer{
            position:absolute; inset:0;
            background: linear-gradient(120deg, transparent 0%, rgba(255,255,255,0.18) 18%, transparent 33%);
            background-size:200% 100%;
            animation:shimmer 6s linear infinite;
            pointer-events:none;
        }
        @keyframes shimmer{
            0%{ background-position:-200% 0; }
            100%{ background-position:200% 0; }
        }

        /* Contenido tarjeta en 2 columnas (responsive) */
        .card__content{
            display:grid;
            grid-template-columns: 1fr 1.1fr;
            gap:0;
        }
        .card__aside{
            background:#fff;
            padding:28px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .brand{
            display:flex; flex-direction:column; align-items:center; gap:14px;
            text-align:center;
        }
        .brand__logo{
            width:min(260px, 70%);
            height:auto;
            filter: drop-shadow(0 6px 20px rgba(16, 24, 40, 0.15));
        }
        .brand__caption{
            font-size:14px; color:var(--color-text-2);
        }

        .card__form{
            background:linear-gradient(135deg, rgba(26,75,150,0.06), rgba(45,90,160,0.06));
            padding:28px;
        }

        /* Texto introductorio */
        .lead{
            font-size:14px;
            color:var(--color-text-2);
            margin:0 0 16px 0;
        }

        /* Inputs profesionales */
        .form-group{ margin-bottom:16px; }
        .label{
            display:block;
            font-size:14px;
            font-weight:500;
            margin-bottom:8px;
            color:var(--color-text);
        }
        .professional-input{
            width:100%;
            padding:16px 20px;
            border:2px solid #e5e7eb;
            border-radius:12px;
            background:#ffffff;
            color:var(--color-text);
            box-shadow:0 2px 4px rgba(0,0,0,0.05);
            transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        }
        .professional-input::placeholder{ color:#9aa3af; }
        .professional-input:hover{ transform:translateY(-1px); }
        .professional-input:focus{
            outline:none;
            border-color:var(--color-accent);
            box-shadow:0 8px 24px rgba(169,202,72,0.18);
            transform:translateY(-1px);
        }

        /* Links corporativos */
        .corporate-link{
            position:relative;
            color:var(--color-primary);
            text-decoration:none;
            font-weight:600;
            transition: transform .15s ease, color .15s ease;
        }
        .corporate-link::after{
            content:"";
            position:absolute; left:50%; bottom:-2px; width:0; height:2px;
            background:var(--color-accent);
            transition: all .2s ease;
        }
        .corporate-link:hover{
            color:var(--color-accent);
            transform: translateY(-1px);
        }
        .corporate-link:hover::after{
            left:0; width:100%;
        }

        /* Botón principal corporativo */
        .corporate-button{
            appearance:none; border:none; cursor:pointer;
            display:inline-flex; align-items:center; justify-content:center;
            gap:10px; padding:16px 24px; border-radius:12px;
            font-weight:600; letter-spacing:.5px; text-transform:uppercase;
            color:#fff;
            background: linear-gradient(135deg, var(--color-accent) 0%, var(--color-accent-2) 100%);
            box-shadow: 0 6px 20px rgba(169, 202, 72, 0.25);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
            position:relative; overflow:hidden;
        }
        .corporate-button:hover{
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(169, 202, 72, 0.32);
            filter: brightness(1.03);
        }
        .corporate-button:active{ transform: translateY(0); }

        /* Ripple effect (se dibuja con JS agregando .ripple) */
        .ripple{
            position:absolute; border-radius:50%;
            transform:scale(0); animation:ripple .6s linear;
            background: rgba(255,255,255,0.55);
            pointer-events:none;
        }
        @keyframes ripple{
            to{ transform:scale(4); opacity:0; }
        }

        /* Alertas */
        .alert{
            border-radius:8px;
            padding:12px 14px;
            font-size:14px;
            margin:10px 0 0 0;
            border:1px solid transparent;
        }
        .alert-success{
            background: rgba(169,202,72,0.10);
            border-color: rgba(169,202,72,0.25);
            color:#2b5310;
        }
        .alert-error{
            background: rgba(239, 68, 68, 0.10);
            border-color: rgba(239, 68, 68, 0.25);
            color:#7a1c1c;
        }

        /* Footer de acciones */
        .form-actions{
            display:flex; justify-content:flex-end; gap:12px; margin-top:18px;
        }

        /* Parallax/tilt sutil (máx 3°) */
        .tilt{
            transform: rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg));
            transition: transform .12s ease;
            will-change: transform;
        }

        /* Responsive */
        @media (max-width: 768px){
            .card__content{ grid-template-columns: 1fr; }
            .card__aside{ order:1; padding:22px; }
            .card__form{ order:2; padding:22px; }
            .form-actions{ justify-content:stretch; }
            .corporate-button{ width:100%; }
        }
        @media (max-width: 480px){
            .card__header{ padding:22px; }
            .card__title{ font-size:18px; }
        }
    </style>
</head>
<body>
    <div class="bg-animated"></div>
    <div class="bg-geo">
        <div class="geo g1"></div>
        <div class="geo g2"></div>
        <div class="geo g3"></div>
        <div class="geo g4"></div>
    </div>

    <main class="page">
        <section class="card tilt" id="tiltCard" aria-label="Card de recuperación de contraseña">
            <header class="card__header">
                <h1 class="card__title">Recuperar contraseña</h1>
                <span class="shimmer" aria-hidden="true"></span>
            </header>

            <div class="card__content">
                {{-- Columna izquierda: branding --}}
                <aside class="card__aside">
                    <div class="brand">
                        {{-- Ajusta la ruta del logo si es necesario --}}
                        <img class="brand__logo" src="{{ asset('images/logos/logo_TotalGas_ver.png') }}" alt="TotalGas" />
                        <p class="brand__caption">Portal de Proveedores</p>
                    </div>
                </aside>

                {{-- Columna derecha: formulario --}}
                <div class="card__form">
                    {{-- Mensaje introductorio --}}
                    <p class="lead">
                        ¿Olvidaste tu contraseña? No hay problema. Ingresa tu correo y te enviaremos un enlace para restablecerla de forma segura.
                    </p>

                    {{-- Estado de sesión: éxito al enviar el enlace --}}
                    @if (session('status'))
                        <div class="alert alert-success" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" novalidate>
                        @csrf

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email" class="label">Correo electrónico</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="professional-input"
                                placeholder="tucorreo@empresa.com"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                inputmode="email"
                            >
                            {{-- Errores --}}
                            @error('email')
                                <div class="alert alert-error" role="alert" style="margin-top:10px;">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Acciones --}}
                        <div class="form-actions">
                            <a href="{{ route('login') }}" class="corporate-link" aria-label="Volver a iniciar sesión">
                                &larr; Volver a iniciar sesión
                            </a>

                            <button type="submit" class="corporate-button" id="sendLinkBtn" aria-label="Enviar enlace de restablecimiento">
                                Enviar enlace
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        // Parallax/tilt sutil (máx 3°)
        (function(){
            const card = document.getElementById('tiltCard');
            const maxDeg = 3;
            let rafId = null;

            function onMove(e){
                const rect = card.getBoundingClientRect();
                const cx = rect.left + rect.width/2;
                const cy = rect.top + rect.height/2;
                const dx = (e.clientX - cx) / (rect.width/2);
                const dy = (e.clientY - cy) / (rect.height/2);
                const ry = Math.max(Math.min(dx * maxDeg, maxDeg), -maxDeg);
                const rx = Math.max(Math.min(-dy * maxDeg, maxDeg), -maxDeg);

                if(rafId) cancelAnimationFrame(rafId);
                rafId = requestAnimationFrame(()=>{
                    card.style.setProperty('--rx', rx + 'deg');
                    card.style.setProperty('--ry', ry + 'deg');
                });
            }
            function resetTilt(){
                card.style.setProperty('--rx', '0deg');
                card.style.setProperty('--ry', '0deg');
            }
            window.addEventListener('mousemove', onMove, { passive:true });
            window.addEventListener('mouseleave', resetTilt);
        })();

        // Ripple en botón principal
        (function(){
            const btn = document.getElementById('sendLinkBtn');
            if(!btn) return;
            btn.addEventListener('click', function(e){
                const rect = this.getBoundingClientRect();
                const ripple = document.createElement('span');
                const size = Math.max(rect.width, rect.height);
                ripple.className = 'ripple';
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (e.clientX - rect.left - size/2) + 'px';
                ripple.style.top = (e.clientY - rect.top - size/2) + 'px';
                this.appendChild(ripple);
                setTimeout(()=>ripple.remove(), 600);
            });
        })();
    </script>
</body>
</html>
