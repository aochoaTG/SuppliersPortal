{{--
    DIRECTIVAS DE SPATIE DISPONIBLES:
    @auth - Usuario autenticado
    @guest - Usuario no autenticado
    @hasrole('role') - Usuario tiene rol específico
    @hasanyrole('role1|role2|role3') - Usuario tiene cualquiera de esos roles
    @hasallroles('role1|role2') - Usuario tiene todos esos roles
    @role('role') - Alias de @hasrole
    @unlessrole('role') - Usuario NO tiene el rol

    @cannot('permission') - Usuario NO tiene permiso
    @canany(['permission1', 'permission2']) - Usuario tiene cualquiera de esos permisos

    EJEMPLOS DE USO ADICIONALES:

    Mostrar contenido solo si NO tiene un rol:
    @unlessrole('supplier')
        <li>Solo para no-proveedores</li>
    @endunlessrole

    Mostrar contenido basado en permisos:

        <li><a href="javascript: void(0);">Crear Post</a></li>
    @endcan

    Combinar condiciones:
    @hasrole('superadmin')

            <li><a href="javascript: void(0);">Gestionar Usuarios</a></li>

    @endhasrole
--}}
<!-- Sidenav Menu Start -->
<div class="sidenav-menu">
    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="TotalGas"
                    width="190" style="min-height:55px!important;"></span>
            <span class="logo-sm"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}"
                    alt="small logo"></span>
        </span>
        <span class="logo-dark">
            <span class="logo-lg"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}" alt="TotalGas"
                    width="190" style="min-height:55px!important;"></span>
            <span class="logo-sm"><img src="{{ asset('images/logos/logo_TotalGas_hor_azul.png') }}"
                    alt="small logo"></span>
        </span>
    </a>

    <button class="button-sm-hover" aria-label="Toggle mini sidebar">
        <i class="ti ti-circle align-middle" aria-hidden="true"></i>
    </button>

    <button class="button-close-fullsidebar" aria-label="Close sidebar">
        <i class="ti ti-x align-middle" aria-hidden="true"></i>
    </button>

    <div data-simplebar>
        <ul class="side-nav">
            {{-- INICIO --}}

            @hasanyrole('superadmin|buyer')
                <li class="side-nav-title">INICIO</li>
                <li class="side-nav-item">
                    <a class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <span class="menu-icon"><i class="ti ti-home"></i></span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                {{-- CATÁLOGOS --}}
                <li class="side-nav-title">CATÁLOGOS</li>

                <li class="side-nav-item">
                    <a class="side-nav-link {{ request()->routeIs('users.staff.index') ? 'active' : '' }}"
                        href="{{ route('users.staff.index') }}">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text">Usuarios Staff</span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a class="side-nav-link {{ request()->routeIs('users.suppliers.index') ? 'active' : '' }}"
                        href="{{ route('users.suppliers.index') }}">
                        <span class="menu-icon"><i class="ti ti-user-cog"></i></span>
                        <span class="menu-text">Usuarios Proveedor</span>
                    </a>
                </li>

                @php
                    $openCatProv = request()->routeIs('suppliers.*') || request()->routeIs('efos.*');
                @endphp
                <li class="side-nav-item">
                    <a class="side-nav-link {{ $openCatProv ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        href="#sidebarCatProveedores" role="button" aria-expanded="{{ $openCatProv ? 'true' : 'false' }}"
                        aria-controls="sidebarCatProveedores">
                        <span class="menu-icon"><i class="ti ti-building-store"></i></span>
                        <span class="menu-text">Proveedores</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="{{ $openCatProv ? 'show' : '' }} collapse" id="sidebarCatProveedores">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="{{ route('cat-suppliers.index') }}"
                                    class="side-nav-link {{ request()->routeIs('cat-suppliers.index') ? 'active' : '' }}">
                                    <span class="menu-text">Lista de Proveedores</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="{{ route('sat_efos_69b.index') }}"
                                    class="side-nav-link {{ request()->routeIs('sat_efos_69b.index') ? 'active' : '' }}">
                                    <span class="menu-text">Listado EFOS</span>
                                </a>
                            </li>
                            <li class="side-nav-item">
                                <a href="{{ route('sirocs.index') }}"
                                    class="side-nav-link {{ request()->routeIs('sirocs.index') ? 'active' : '' }}">
                                    <span class="menu-text">Listado SIROC</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- COMPRAS --}}
                <li class="side-nav-title">COMPRAS</li>

                @php
                    $openReq = request()->routeIs('requisitions.*') || request()->routeIs('requisition-items.*');
                @endphp
                <li class="side-nav-item">
                    <a class="side-nav-link {{ $openReq ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        href="#sidebarComprasRequisiciones" role="button"
                        aria-expanded="{{ $openReq ? 'true' : 'false' }}" aria-controls="sidebarComprasRequisiciones">
                        <span class="menu-icon"><i class="ti ti-clipboard-list"></i></span>
                        <span class="menu-text">Requisiciones</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <div class="{{ $openReq ? 'show' : '' }} collapse" id="sidebarComprasRequisiciones">
                        <ul class="sub-menu">

                            <li class="side-nav-item">
                                <a href="{{ route('requisitions.index') }}"
                                    class="side-nav-link {{ request()->routeIs('requisitions.index') ? 'active' : '' }}">
                                    <span class="menu-text">Listado</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="{{ route('requisitions.inbox.review') }}"
                                    class="side-nav-link {{ request()->routeIs('requisitions.inbox.review') ? 'active' : '' }}">
                                    <span class="menu-text">Buzon de Revisión</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="{{ route('requisitions.inbox.approval') }}"
                                    class="side-nav-link {{ request()->routeIs('requisitions.inbox.approval') ? 'active' : '' }}">
                                    <span class="menu-text">Buzon de Aprobación</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="{{ route('requisitions.inbox.rejected') }}"
                                    class="side-nav-link {{ request()->routeIs('requisitions.inbox.rejected') ? 'active' : '' }}">
                                    <span class="menu-text">Rechazadas</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item">
                    <a href="javascript: void(0);"
                        class="side-nav-link {{ request()->routeIs('po.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
                        <span class="menu-text">Órdenes de Compra</span>
                    </a>
                </li>

                {{-- FINANZAS --}}
                <li class="side-nav-title">FINANZAS</li>
                @php
                    $openFin =
                        request()->routeIs('invoices.*') ||
                        request()->routeIs('payments.*') ||
                        request()->routeIs('finance-reports.*');
                @endphp
                <li class="side-nav-item">
                    <a class="side-nav-link {{ $openFin ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        href="#sidebarFinanzas" role="button" aria-expanded="{{ $openFin ? 'true' : 'false' }}"
                        aria-controls="sidebarFinanzas">
                        <span class="menu-icon"><i class="ti ti-cash"></i></span>
                        <span class="menu-text">Pagos y Facturación</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="{{ $openFin ? 'show' : '' }} collapse" id="sidebarFinanzas">
                        <ul class="sub-menu">

                            <li class="side-nav-item">
                                <a href="javascript: void(0);"
                                    class="side-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                                    <span class="menu-text">Facturas</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="javascript: void(0);"
                                    class="side-nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                                    <span class="menu-text">Pagos</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="javascript: void(0);"
                                    class="side-nav-link {{ request()->routeIs('finance-reports.*') ? 'active' : '' }}">
                                    <span class="menu-text">Reportes</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('cost-centers.index') }}"
                        class="side-nav-link {{ request()->routeIs('cost-centers.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-hierarchy-2"></i></span>
                        <span class="menu-text">Centros de Costo</span>
                    </a>
                </li>
                {{-- En tu sidebar Zircos --}}
                <li class="side-nav-item">
                    <a href="{{ route('categories.index') }}"
                        class="side-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-category"></i></span>
                        <span class="menu-text">Categorias</span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('annual-budgets.index') }}"
                        class="side-nav-link {{ request()->routeIs('annual-budgets.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-cash"></i></span>
                        <span class="menu-text">Presupuestos</span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('budget-movements.index') }}"
                        class="side-nav-link {{ request()->routeIs('budget-movements.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <i class="ti ti-activity"></i>
                        </span>
                        <span class="menu-text">Movimientos Presupuestales</span>
                    </a>
                </li>

                {{-- PROVEEDORES --}}
                <li class="side-nav-title">PROVEEDORES</li>

                <li class="side-nav-item">
                    <a href="{{ route('admin.review.index') }}"
                        class="side-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-checklist"></i></span>
                        <span class="menu-text">Rev. de documentos</span>

                        @if (!empty($pendingReviewCount) && $pendingReviewCount > 0)
                            <span class="badge bg-danger rounded-pill ms-auto">
                                {{ $pendingReviewCount }}
                            </span>
                        @endif
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('admin.announcements.index') }}"
                        class="side-nav-link {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
                        <span class="menu-text">Comunicados</span>
                    </a>
                </li>

                @php
                    $openConfiguration =
                        request()->routeIs('companies.*') ||
                        request()->routeIs('stations.*') ||
                        request()->routeIs('departments.*') ||
                        request()->routeIs('taxes.*');
                @endphp

                {{-- CONFIGURACIÓN --}}
                <li class="side-nav-title">CONFIGURACIÓN</li>
                <li class="side-nav-item">
                    <a class="side-nav-link {{ $openConfiguration ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                        href="#sidebarConfigurations" role="button"
                        aria-expanded="{{ $openConfiguration ? 'true' : 'false' }}"
                        aria-controls="sidebarConfigurations">
                        <span class="menu-icon"><i class="ti ti-cash"></i></span>
                        <span class="menu-text">Catálogos</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="{{ $openConfiguration ? 'show' : '' }} collapse" id="sidebarConfigurations">
                        <ul class="sub-menu">
                            <li class="side-nav-item">
                                <a href="{{ route('companies.index') }}"
                                    class="side-nav-link {{ request()->routeIs('companies.index') ? 'active' : '' }}">
                                    <span class="menu-text">Empresas</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="{{ route('stations.index') }}"
                                    class="side-nav-link {{ request()->routeIs('stations.index') ? 'active' : '' }}">
                                    <span class="menu-text">Estaciones</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="{{ route('departments.index') }}"
                                    class="side-nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                                    <span class="menu-text">Departamentos</span>
                                </a>
                            </li>

                            <li class="side-nav-item">
                                <a href="{{ route('taxes.index') }}"
                                    class="side-nav-link {{ request()->routeIs('taxes.index') ? 'active' : '' }}">
                                    <span class="menu-text">IVA</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item text-danger">
                    <a href="{{ route('incidents.index') }}"
                        class="side-nav-link text-danger {{ request()->routeIs('system.settings') ? 'active' : '' }}">
                        <span class="menu-icon"><i class="ti ti-bug"></i></span>
                        <span class="menu-text">Incidentes Reportados</span>
                    </a>
                </li>

            @endhasanyrole

            @hasanyrole('supplier')

                {{-- PROVEEDORES --}}
                <li class="side-nav-title">PROVEEDORES</li>

                @php($u = auth()->user())

                @if ($u->mustFinishSupplierOnboarding())
                    <li class="side-nav-item">
                        <a href="{{ route('documents.suppliers.index') }}"
                            class="side-nav-link {{ request()->routeIs('documents.suppliers.index') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="ti ti-checklist"></i></span>
                            <span class="menu-text">Documentación</span>
                            <span class="badge bg-danger rounded-pill">!</span>
                        </a>
                    </li>
                @else
                    <li class="side-nav-item">
                        <a href="{{ route('documents.suppliers.index') }}"
                            class="side-nav-link {{ request()->routeIs('documents.suppliers.index') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="ti ti-checklist"></i></span>
                            <span class="menu-text">Documentación</span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="{{ route('supplier.announcements.inbox') }}"
                            class="side-nav-link {{ request()->routeIs('supplier.announcements.inbox') ? 'active' : '' }}">
                            <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
                            <span class="menu-text">Comunicados</span>
                        </a>
                    </li>
                @endif
            @endhasanyrole

        </ul>

        <!-- Help Box -->
        <div class="help-box text-center">
            <h5 class="fw-semibold fs-16">¿Necesitas ayuda?</h5>
            <p class="text-muted mb-3">Si el sistema no funciona como esperabas, por favor contáctanos.</p>
            <a href="javascript: void(0);" data-bs-toggle="modal" data-bs-target="#reportIssueModal"
                class="btn btn-danger btn-sm">Contáctanos</a>
        </div>

        <div class="clearfix"></div>
    </div>
</div>
