<!-- Topbar Start -->
<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="{{ asset('images/logos/logo_TotalGas_hor_verde.png') }}" alt="logo"></span>
                    <span class="logo-sm"><img src="{{ asset('images/logos/logo_TotalGas_hor_verde.png') }}" alt="small logo"></span>
                </span>

                <span class="logo-dark">
                    <span class="logo-lg"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="dark logo"></span>
                    <span class="logo-sm"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="small logo"></span>
                </span>
            </a>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn-icon rounded-circle btn btn-light">
                <i class="ti ti-menu-2 fs-22"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="ti ti-menu-2 fs-22"></i>
            </button>


            <!-- Mega Menu Dropdown -->
            <div class="topbar-item d-none d-md-flex">
                <div class="dropdown">
                    <a href="https://totalgas.com/" target="_blank">
                        TOTALGAS
                    </a>
                </div> <!-- .dropdown-->
            </div> <!-- end topbar-item -->
        </div>

        <div class="d-flex align-items-center gap-2">

            <!-- Notification Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,23" type="button" data-bs-auto-close="outside" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-bell-z fs-24"></i>
                        <span class="noti-icon-badge badge text-bg-danger">02</span>
                    </button>

                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg fs-13">
                        <div class="py-2 px-3 border-bottom border-dashed">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-16 fw-semibold"> Notificaciones</h6>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle drop-arrow-none link-dark" data-bs-toggle="dropdown" data-bs-offset="0,15" aria-expanded="false">
                                            <i class="ti ti-settings fs-22 align-middle"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Mark as Read</a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Delete All</a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Do not Disturb</a>
                                            <!-- item-->
                                            <a href="javascript:void(0);" class="dropdown-item">Other Settings</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative z-2 shadow-none rounded-0" style="max-height: 300px;" data-simplebar>
                            <!-- Document Approval Notification -->
                            <div class="dropdown-item notification-item py-2 text-wrap active" id="notification-1">
                                <span class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-title text-bg-success rounded-circle fs-22">
                                            <i class="ti ti-file-check"></i>
                                        </span>
                                    </div>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Documento Aprobado</span> El documento <span class="fw-medium text-body">"Informe Trimestral Q4 2023"</span> ha sido aprobado
                                        <br />
                                        <span class="fs-12">Hace 25 min</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button" class="btn btn-ghost-danger rounded-circle btn-sm btn-icon" data-dismissible="#notification-1">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- Document Review Request -->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-2">
                                <span class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                            <i class="ti ti-edit"></i>
                                        </span>
                                    </div>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Revisión Solicitada</span> Se te ha asignado la revisión del documento <span class="fw-medium text-body">"Contrato Proveedor 2024"</span>
                                        <br />
                                        <span class="fs-12">Hace 1h</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button" class="btn btn-ghost-danger rounded-circle btn-sm btn-icon" data-dismissible="#notification-2">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- New Document Upload -->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-3">
                                <span class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-title text-bg-info rounded-circle fs-22">
                                            <i class="ti ti-upload"></i>
                                        </span>
                                    </div>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Nuevo Documento</span> <span class="fw-medium text-body">Carlos M.</span> ha subido un nuevo archivo: <span class="fw-medium text-body">"Presupuesto_2024.xlsx"</span>
                                        <br />
                                        <span class="fs-12">Hace 2h</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button" class="btn btn-ghost-danger rounded-circle btn-sm btn-icon" data-dismissible="#notification-3">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>

                            <!-- Document Expiration Warning -->
                            <div class="dropdown-item notification-item py-2 text-wrap" id="notification-4">
                                <span class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-title text-bg-danger rounded-circle fs-22">
                                            <i class="ti ti-alert-triangle"></i>
                                        </span>
                                    </div>
                                    <span class="flex-grow-1 text-muted">
                                        <span class="fw-medium text-body">Documento por Vencer</span> El certificado <span class="fw-medium text-body">"ISO 9001:2015"</span> vencerá en 15 días
                                        <br />
                                        <span class="fs-12">Ayer</span>
                                    </span>
                                    <span class="notification-item-close">
                                        <button type="button" class="btn btn-ghost-danger rounded-circle btn-sm btn-icon" data-dismissible="#notification-4">
                                            <i class="ti ti-x fs-16"></i>
                                        </button>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- All-->
                        <a href="javascript:void(0);" class="dropdown-item notification-item text-center text-reset text-decoration-underline link-offset-2 fw-bold notify-item border-top border-light py-2">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            <!-- Button Trigger Customizer Offcanvas -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" type="button">
                    <i class="ti ti-settings fs-22"></i>
                </button>
            </div>

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i class="ti ti-moon fs-22"></i>
                </button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown" data-bs-offset="0,19" type="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{ Auth::user()?->avatar ? asset('storage/' . Auth::user()?->avatar) : asset('images/users/avatar-1.jpg') }}" width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image">
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            <h5 class="my-0 fs-13 fw-semibold">{{ Auth::user()?->name }}</h5>
                        </span>
                        <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">¡Bienvenido, {{ Auth::user()?->name }}!</h6>
                        </div>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#profileModal">
                            <i class="ti ti-user-hexagon me-1 fs-17 align-middle"></i>
                            <span class="align-middle">Mi cuenta</span>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#reportIssueModal">
                            <i class="ti ti-lifebuoy me-1 fs-17 align-middle"></i>
                            <span class="align-middle">Soporte</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <form id="lock-form" method="POST" action="{{ route('lockscreen.lock') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="ti ti-lock-square-rounded me-1 fs-17 align-middle"></i> Bloquear pantalla</button>
                        </form>

                        <!-- item-->
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item fw-semibold text-danger">
                                <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                <span class="align-middle">Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Topbar End -->

<!-- Theme Settings Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas" aria-labelledby="themeSettingsLabel">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title" id="themeSettingsLabel">
            <i class="ti ti-settings me-2 text-primary"></i>Configuración
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <div class="offcanvas-body p-0" data-simplebar>

        {{-- ── Modo Claro / Oscuro ─────────────────────────────── --}}
        <div class="p-3 border-bottom">
            <p class="fw-semibold text-uppercase text-muted fs-11 mb-2">Modo</p>
            <div class="btn-group w-100" role="group" aria-label="Modo de color">
                <input type="radio" class="btn-check" name="data-bs-theme"
                       id="layout-mode-light" value="light" autocomplete="off">
                <label class="btn btn-outline-secondary" for="layout-mode-light">
                    <i class="ti ti-sun me-1"></i>Claro
                </label>

                <input type="radio" class="btn-check" name="data-bs-theme"
                       id="layout-mode-dark" value="dark" autocomplete="off">
                <label class="btn btn-outline-secondary" for="layout-mode-dark">
                    <i class="ti ti-moon me-1"></i>Oscuro
                </label>
            </div>
        </div>

        {{-- ── Tamaño del Sidebar ───────────────────────────────── --}}
        <div class="p-3 border-bottom">
            <p class="fw-semibold text-uppercase text-muted fs-11 mb-2">Tamaño del Sidebar</p>
            <div class="btn-group w-100" role="group" aria-label="Tamaño del sidebar">
                <input type="radio" class="btn-check" name="data-sidenav-size"
                       id="sidenav-size-default" value="default" autocomplete="off">
                <label class="btn btn-outline-secondary" for="sidenav-size-default">
                    <i class="ti ti-layout-sidebar me-1"></i>Normal
                </label>

                <input type="radio" class="btn-check" name="data-sidenav-size"
                       id="sidenav-size-compact" value="compact" autocomplete="off">
                <label class="btn btn-outline-secondary" for="sidenav-size-compact">
                    <i class="ti ti-layout-sidebar-right-collapse me-1"></i>Compacto
                </label>
            </div>
        </div>

        {{-- ── Colores (solo modo claro) ──────────────────────────── --}}
        <div class="light-only-section">

            {{-- Aviso --}}
            <div class="px-3 pt-3 pb-0">
                <p class="text-muted mb-0" style="font-size:10px;line-height:1.4;">
                    <i class="ti ti-info-circle me-1"></i>
                    Las opciones de color solo aplican en modo <strong>claro</strong>.
                </p>
            </div>

            {{-- Color del Topbar --}}
            <div class="p-3 border-bottom">
                <p class="fw-semibold text-uppercase text-muted fs-11 mb-2">Color del Topbar</p>
                <div class="btn-group w-100" role="group" aria-label="Color del topbar">
                    <input type="radio" class="btn-check" name="data-topbar-color"
                           id="topbar-color-light" value="light" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="topbar-color-light">
                        <i class="ti ti-sun me-1"></i>Claro
                    </label>

                    <input type="radio" class="btn-check" name="data-topbar-color"
                           id="topbar-color-dark" value="dark" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="topbar-color-dark">
                        <i class="ti ti-moon me-1"></i>Oscuro
                    </label>
                </div>
            </div>

            {{-- Color del Sidebar --}}
            <div class="p-3 border-bottom">
                <p class="fw-semibold text-uppercase text-muted fs-11 mb-2">Color del Sidebar</p>
                <div class="btn-group w-100" role="group" aria-label="Color del sidebar">
                    <input type="radio" class="btn-check" name="data-menu-color"
                           id="menu-color-light" value="light" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="menu-color-light">
                        <i class="ti ti-sun me-1"></i>Claro
                    </label>

                    <input type="radio" class="btn-check" name="data-menu-color"
                           id="menu-color-dark" value="dark" autocomplete="off">
                    <label class="btn btn-outline-secondary" for="menu-color-dark">
                        <i class="ti ti-moon me-1"></i>Oscuro
                    </label>
                </div>
            </div>

        </div>{{-- /light-only-section --}}

        {{-- ── Zoom / Accesibilidad ─────────────────────────────── --}}
        <div class="p-3 border-bottom">
            <p class="fw-semibold text-uppercase text-muted fs-11 mb-2">
                <i class="ti ti-accessible me-1"></i>Zoom de Página
            </p>
            <div class="d-flex align-items-center gap-2">
                <button id="zoom-out" type="button"
                        class="btn btn-outline-secondary btn-sm flex-shrink-0"
                        style="width:36px;height:36px;padding:0;" title="Reducir zoom">
                    <i class="ti ti-minus fs-16"></i>
                </button>

                <div class="flex-grow-1 text-center">
                    <span id="zoom-display"
                          class="fw-bold fs-16 text-body">100%</span>
                    <div class="text-muted" style="font-size:10px;line-height:1.2;">70% – 130%</div>
                </div>

                <button id="zoom-reset" type="button"
                        class="btn btn-outline-secondary btn-sm flex-shrink-0 px-2"
                        title="Restablecer zoom al 100%">
                    <i class="ti ti-refresh fs-14 me-1"></i><span style="font-size:11px;">100%</span>
                </button>

                <button id="zoom-in" type="button"
                        class="btn btn-outline-secondary btn-sm flex-shrink-0"
                        style="width:36px;height:36px;padding:0;" title="Aumentar zoom">
                    <i class="ti ti-plus fs-16"></i>
                </button>
            </div>
        </div>

        {{-- ── Botón restablecer todo ───────────────────────────── --}}
        <div class="p-3">
            <button type="button" class="btn btn-outline-danger btn-sm w-100" id="reset-theme-settings">
                <i class="ti ti-restore me-1"></i>Restablecer todo
            </button>
        </div>

    </div>{{-- /offcanvas-body --}}
</div>
<!-- End Theme Settings Offcanvas -->