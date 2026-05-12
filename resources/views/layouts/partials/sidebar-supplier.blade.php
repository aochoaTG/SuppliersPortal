{{--
    sidebar-supplier.blade.php
    ==========================
    Sidebar for supplier (proveedor) users only.

    ROLES HANDLED:
      - supplier : all supplier accounts

    ONBOARDING GATE:
      If $user->mustFinishSupplierOnboarding() returns true, only "Documentación"
      is shown with a danger badge, forcing the supplier to complete their profile
      before accessing any other feature.

    HOW TO ADD A NEW SECTION:
      1. Add the <li> block inside the post-onboarding @else block below.
      2. If the feature is not yet implemented, add it as a commented-out block
         with a note:
      3. Update this header comment to document the new section.

    SECTIONS:
      - Dashboard
      - Cotizaciones (RFQ) → Mis RFQs, Historial
      - Documentación
      - Comunicados
      - Mis Entregas
      - Facturación [PENDING — not yet implemented]
--}}

@php $u = auth()->user(); @endphp

{{-- ═══════════════════════════════════════════════════
     PORTAL DE PROVEEDORES — visible to: supplier
     ═══════════════════════════════════════════════════ --}}
<li class="side-nav-title">PORTAL DE PROVEEDORES</li>

@if ($u->mustFinishSupplierOnboarding())
    {{-- Onboarding incomplete: only Documentación is shown with an alert badge --}}
    <li class="side-nav-item">
        <a href="{{ route('documents.suppliers.index') }}"
            class="side-nav-link {{ request()->routeIs('documents.suppliers.index') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-checklist"></i></span>
            <span class="menu-text">Documentación</span>
            <span class="badge bg-danger rounded-pill">!</span>
        </a>
    </li>

@else
    {{-- Dashboard --}}
    <li class="side-nav-item">
        <a href="{{ route('supplier.dashboard') }}"
            class="side-nav-link {{ request()->routeIs('supplier.dashboard') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-home"></i></span>
            <span class="menu-text">Dashboard</span>
        </a>
    </li>

    {{-- Cotizaciones (RFQ) accordion --}}
    @php $openSupplierRfq = request()->routeIs('supplier.rfq.*') || request()->routeIs('supplier.quotations.*'); @endphp
    <li class="side-nav-item">
        <a class="side-nav-link {{ $openSupplierRfq ? '' : 'collapsed' }}"
            data-bs-toggle="collapse"
            href="#sidebarSupplierRfq"
            role="button"
            aria-expanded="{{ $openSupplierRfq ? 'true' : 'false' }}"
            aria-controls="sidebarSupplierRfq">
            <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
            <span class="menu-text">Cotizaciones</span>
            <span class="menu-arrow"></span>
        </a>
        <div class="{{ $openSupplierRfq ? 'show' : '' }} collapse" id="sidebarSupplierRfq">
            <ul class="sub-menu">
                {{-- Mis RFQs --}}
                <li class="side-nav-item">
                    <a href="{{ route('supplier.dashboard') }}"
                        class="side-nav-link {{ request()->routeIs('supplier.dashboard') || request()->routeIs('supplier.rfq.show') ? 'active' : '' }}">
                        <span class="menu-text">Mis RFQs</span>
                    </a>
                </li>
                {{-- Historial --}}
                <li class="side-nav-item">
                    <a href="{{ route('supplier.quotations.history') }}"
                        class="side-nav-link {{ request()->routeIs('supplier.quotations.history') ? 'active' : '' }}">
                        <span class="menu-text">Historial</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>

    {{-- Documentación --}}
    <li class="side-nav-item">
        <a href="{{ route('documents.suppliers.index') }}"
            class="side-nav-link {{ request()->routeIs('documents.suppliers.*') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-checklist"></i></span>
            <span class="menu-text">Documentación</span>
        </a>
    </li>

    {{-- Comunicados --}}
    <li class="side-nav-item">
        <a href="{{ route('supplier.announcements.inbox') }}"
            class="side-nav-link {{ request()->routeIs('supplier.announcements.inbox') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
            <span class="menu-text">Comunicados</span>
        </a>
    </li>

    {{-- Mis Entregas --}}
    <li class="side-nav-item">
        <a href="{{ route('supplier.deliveries.index') }}"
            class="side-nav-link {{ request()->routeIs('supplier.deliveries.*') ? 'active' : '' }}">
            <span class="menu-icon"><i class="ti ti-truck-delivery"></i></span>
            <span class="menu-text">Mis Entregas</span>
        </a>
    </li>

    {{-- PENDING: Facturación (PDF + XML upload)
         Uncomment and implement routes when the invoice upload feature is built.
         Required routes: supplier.invoices.create, supplier.invoices.index
    --}}
    {{--
    @php $openFacturacion = request()->routeIs('supplier.invoices.*'); @endphp
    <li class="side-nav-item">
        <a class="side-nav-link {{ $openFacturacion ? '' : 'collapsed' }}"
            data-bs-toggle="collapse"
            href="#sidebarSupplierFacturacion"
            role="button"
            aria-expanded="{{ $openFacturacion ? 'true' : 'false' }}"
            aria-controls="sidebarSupplierFacturacion">
            <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
            <span class="menu-text">Facturación</span>
            <span class="menu-arrow"></span>
        </a>
        <div class="{{ $openFacturacion ? 'show' : '' }} collapse" id="sidebarSupplierFacturacion">
            <ul class="sub-menu">
                <li class="side-nav-item">
                    <a href="{{ route('supplier.invoices.create') }}" class="side-nav-link">
                        <span class="menu-text">Cargar Factura</span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('supplier.invoices.index') }}" class="side-nav-link">
                        <span class="menu-text">Historial de Facturas</span>
                    </a>
                </li>
            </ul>
        </div>
    </li>
    --}}

@endif
