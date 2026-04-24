{{--
    sidebar-staff.blade.php
    =======================
    Sidebar for all TotalGas internal staff roles.

    ROLES HANDLED:
      - superadmin     : full access to all sections
      - staff          : Compras + Proveedores
      - accounting     : Finanzas only
      - general_director : Compras + Finanzas
      - authorizer     : Compras → Aprobar cotización only
      - catalog_admin  : Compras → Productos/Servicios only
      - requester      : Compras → Requisiciones + Órdenes de Compra

    HOW TO ADD A NEW SECTION:
      1. Add a new section block wrapped with @hasanyrole('role1|role2|...')
      2. Add a plain comment above the block listing the allowed roles.
      3. Update the ROLES HANDLED list above if a new role is introduced.

    HOW TO ADD AN ITEM INSIDE AN EXISTING SECTION:
      1. Add the <li> block in the correct section.
      2. If it has different role restrictions than the parent section,
         wrap it with its own @hasanyrole directive and add an inline comment.

    SECTIONS:
      - INICIO          → all roles
      - COMPRAS         → superadmin, staff, requester, general_director, authorizer, catalog_admin
      - FINANZAS        → superadmin, accounting, general_director
      - PROVEEDORES     → superadmin, staff
      - CONFIGURACIÓN   → superadmin only
--}}

{{-- ═══════════════════════════════════════════════════
     INICIO — visible to: all staff roles
     ═══════════════════════════════════════════════════ --}}
<li class="side-nav-title">INICIO</li>
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
        href="{{ route('dashboard') }}">
        <span class="menu-icon"><i class="ti ti-home"></i></span>
        <span class="menu-text">Dashboard</span>
    </a>
</li>

{{-- ═══════════════════════════════════════════════════
     COMPRAS — visible to: superadmin, staff, requester, general_director, authorizer, catalog_admin
     ═══════════════════════════════════════════════════ --}}
@hasanyrole('superadmin|staff|requester|general_director|authorizer|catalog_admin')
<li class="side-nav-title">COMPRAS</li>

{{-- Requisiciones — visible to: superadmin, staff, requester, general_director --}}
@hasanyrole('superadmin|staff|requester|general_director')
<li class="side-nav-item">
    <a href="{{ route('requisitions.index') }}"
        class="side-nav-link {{ request()->routeIs('requisitions.*') || request()->routeIs('requisition-items.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-clipboard-list"></i></span>
        <span class="menu-text">Requisiciones</span>
    </a>
</li>
@endhasanyrole

{{-- Cotizaciones (RFQ) accordion — visible to: superadmin, staff, authorizer, general_director --}}
@hasanyrole('superadmin|staff|authorizer|general_director')
@php $openRfq = request()->routeIs('rfq.*') || request()->routeIs('quotes.*') || request()->routeIs('approvals.quotations.*'); @endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openRfq ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarComprasRfq" role="button"
        aria-expanded="{{ $openRfq ? 'true' : 'false' }}" aria-controls="sidebarComprasRfq">
        <span class="menu-icon"><i class="ti ti-file-invoice"></i></span>
        <span class="menu-text">Cotizaciones (RFQ)</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openRfq ? 'show' : '' }} collapse" id="sidebarComprasRfq">
        <ul class="sub-menu">
            {{-- Cotizar — visible to: superadmin, staff --}}
            @hasanyrole('superadmin|staff')
            @php
                try {
                    $activeRequisitionsCount = \App\Models\Requisition::whereNotIn('status', ['DRAFT', 'CANCELLED', 'COMPLETED'])->count();
                } catch (\Exception $e) {
                    $activeRequisitionsCount = 0;
                }
            @endphp
            <li class="side-nav-item">
                <a href="{{ route('quotes.index') }}"
                    class="side-nav-link {{ request()->routeIs('quotes.index') ? 'active' : '' }}">
                    <span class="menu-text">Cotizar</span>
                    @if($activeRequisitionsCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $activeRequisitionsCount }}</span>
                    @endif
                </a>
            </li>
            @endhasanyrole

            {{-- Aprobar cotización — visible to: superadmin, staff, authorizer, general_director --}}
            @php
                try {
                    $pendingApprovalsCount = \App\Models\QuotationSummary::where('approval_status', 'pending')->count();
                } catch (\Exception $e) {
                    $pendingApprovalsCount = 0;
                }
            @endphp
            <li class="side-nav-item">
                <a href="{{ route('approvals.quotations.index') }}"
                    class="side-nav-link {{ request()->routeIs('approvals.quotations.*') ? 'active' : '' }}">
                    <span class="menu-text">Aprobar cotización</span>
                    @if($pendingApprovalsCount > 0)
                        <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingApprovalsCount }}</span>
                    @endif
                </a>
            </li>

            {{-- Listado de RFQs — visible to: superadmin, staff, general_director --}}
            @hasanyrole('superadmin|staff|general_director')
            <li class="side-nav-item">
                <a href="{{ route('rfq.index') }}"
                    class="side-nav-link {{ request()->routeIs('rfq.index') ? 'active' : '' }}">
                    <span class="menu-text">Listado de RFQs</span>
                </a>
            </li>
            @endhasanyrole

            {{-- Pendientes de Respuesta — visible to: superadmin, staff --}}
            @hasanyrole('superadmin|staff')
            <li class="side-nav-item">
                <a href="{{ route('rfq.inbox.pending') }}"
                    class="side-nav-link {{ request()->routeIs('rfq.inbox.pending') ? 'active' : '' }}">
                    <span class="menu-text">Pendientes de Respuesta</span>
                </a>
            </li>
            @endhasanyrole
        </ul>
    </div>
</li>
@endhasanyrole {{-- end Cotizaciones accordion --}}

{{-- Órdenes de Compra — visible to: superadmin, staff, requester, general_director --}}
@hasanyrole('superadmin|staff|requester|general_director')
<li class="side-nav-item">
    <a href="{{ route('purchase-orders.index') }}"
        class="side-nav-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-shopping-cart"></i></span>
        <span class="menu-text">Órdenes de Compra</span>
        @php
            try {
                $poCount = \App\Models\PurchaseOrder::where('status', 'OPEN')->count();
            } catch (\Exception $e) {
                $poCount = 0;
            }
        @endphp
        @if($poCount > 0)
            <span class="badge bg-info rounded-pill ms-auto">{{ $poCount }}</span>
        @endif
    </a>
</li>
@endhasanyrole

{{-- Recepciones — visible to: superadmin, staff --}}
@hasanyrole('superadmin|staff')
<li class="side-nav-item">
    <a href="{{ route('receptions.overview') }}"
        class="side-nav-link {{ request()->routeIs('receptions.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-truck-delivery"></i></span>
        <span class="menu-text">Recepciones</span>
    </a>
</li>
@endhasanyrole

{{-- Productos/Servicios — visible to: superadmin, staff, catalog_admin --}}
@hasanyrole('superadmin|staff|catalog_admin')
<li class="side-nav-item">
    <a href="{{ route('products-services.index') }}"
        class="side-nav-link {{ request()->routeIs('products-services.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-package"></i></span>
        <span class="menu-text">Productos/Servicios</span>
    </a>
</li>
@endhasanyrole

@endhasanyrole {{-- end COMPRAS --}}

{{-- ═══════════════════════════════════════════════════
     FINANZAS — visible to: superadmin, accounting, general_director
     ═══════════════════════════════════════════════════ --}}
@hasanyrole('superadmin|accounting|general_director')
<li class="side-nav-title">FINANZAS</li>

{{-- Control Presupuestal accordion — visible to: superadmin, accounting, general_director --}}
@php
$openPresupuesto =
    request()->routeIs('annual_budgets.*') ||
    request()->routeIs('budget_monthly_distributions.*') ||
    request()->routeIs('budget_movements.*') ||
    request()->routeIs('cost-centers.*') ||
    request()->routeIs('categories.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openPresupuesto ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarPresupuesto" role="button"
        aria-expanded="{{ $openPresupuesto ? 'true' : 'false' }}" aria-controls="sidebarPresupuesto">
        <span class="menu-icon"><i class="ti ti-chart-bar"></i></span>
        <span class="menu-text">Control Presupuestal</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openPresupuesto ? 'show' : '' }} collapse" id="sidebarPresupuesto">
        <ul class="sub-menu">
            <li class="side-nav-item">
                <a href="{{ route('cost-centers.index') }}"
                    class="side-nav-link {{ request()->routeIs('cost-centers.*') ? 'active' : '' }}">
                    <span class="menu-text">Centros de Costo</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('annual_budgets.index') }}"
                    class="side-nav-link {{ request()->routeIs('annual_budgets.*') ? 'active' : '' }}">
                    <span class="menu-text">Presupuestos Anuales</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('budget_monthly_distributions.index') }}"
                    class="side-nav-link {{ request()->routeIs('budget_monthly_distributions.*') ? 'active' : '' }}">
                    <span class="menu-text">Distribuciones Mensuales</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('budget_movements.index') }}"
                    class="side-nav-link {{ request()->routeIs('budget_movements.*') ? 'active' : '' }}">
                    <span class="menu-text">Movimiento Presupuestal</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('categories.index') }}"
                    class="side-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <span class="menu-text">Categorías</span>
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- Pagos y Facturación accordion — visible to: superadmin, accounting, general_director --}}
@php
$openPagos =
    request()->routeIs('invoices.*') ||
    request()->routeIs('payments.*') ||
    request()->routeIs('finance-reports.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openPagos ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarPagos" role="button" aria-expanded="{{ $openPagos ? 'true' : 'false' }}"
        aria-controls="sidebarPagos">
        <span class="menu-icon"><i class="ti ti-cash"></i></span>
        <span class="menu-text">Pagos y Facturación</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openPagos ? 'show' : '' }} collapse" id="sidebarPagos">
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
                    <span class="menu-text">Reportes Financieros</span>
                </a>
            </li>
        </ul>
    </div>
</li>
@endhasanyrole {{-- end FINANZAS --}}

{{-- ═══════════════════════════════════════════════════
     PROVEEDORES — visible to: superadmin, staff
     ═══════════════════════════════════════════════════ --}}
@hasanyrole('superadmin|staff')
<li class="side-nav-title">PROVEEDORES</li>

{{-- Gestión de Proveedores accordion — visible to: superadmin, staff --}}
@php
$openGestionProv = request()->routeIs('cat-suppliers.*')
    || request()->routeIs('users.suppliers.*')
    || request()->routeIs('sat_efos_69b.*')
    || request()->routeIs('sirocs.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openGestionProv ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarGestionProveedores" role="button"
        aria-expanded="{{ $openGestionProv ? 'true' : 'false' }}"
        aria-controls="sidebarGestionProveedores">
        <span class="menu-icon"><i class="ti ti-building-store"></i></span>
        <span class="menu-text">Gestión de Proveedores</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openGestionProv ? 'show' : '' }} collapse" id="sidebarGestionProveedores">
        <ul class="sub-menu">
            {{-- Lista de Proveedores --}}
            <li class="side-nav-item">
                <a href="{{ route('cat-suppliers.index') }}"
                    class="side-nav-link {{ request()->routeIs('cat-suppliers.*') ? 'active' : '' }}">
                    <span class="menu-text">Lista de Proveedores</span>
                </a>
            </li>
            {{-- Usuarios Proveedor --}}
            <li class="side-nav-item">
                <a href="{{ route('users.suppliers.index') }}"
                    class="side-nav-link {{ request()->routeIs('users.suppliers.*') ? 'active' : '' }}">
                    <span class="menu-text">Usuarios Proveedor</span>
                </a>
            </li>
            {{-- EFOS (SAT) — lista negra del SAT art. 69-B --}}
            <li class="side-nav-item">
                <a href="{{ route('sat_efos_69b.index') }}"
                    class="side-nav-link {{ request()->routeIs('sat_efos_69b.*') ? 'active' : '' }}">
                    <span class="menu-text">EFOS (SAT)</span>
                </a>
            </li>
            {{-- SIROC (IMSS) — Sistema de Información para la Relación con Contratistas --}}
            <li class="side-nav-item">
                <a href="{{ route('sirocs.index') }}"
                    class="side-nav-link {{ request()->routeIs('sirocs.*') ? 'active' : '' }}">
                    <span class="menu-text">SIROC (IMSS)</span>
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- Rev. de documentos — visible to: superadmin, staff --}}
<li class="side-nav-item">
    <a href="{{ route('admin.review.index') }}"
        class="side-nav-link {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-checklist"></i></span>
        <span class="menu-text">Rev. de documentos</span>
        @if (!empty($pendingReviewCount) && $pendingReviewCount > 0)
            <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingReviewCount }}</span>
        @endif
    </a>
</li>

{{-- Comunicados — visible to: superadmin, staff --}}
<li class="side-nav-item">
    <a href="{{ route('admin.announcements.index') }}"
        class="side-nav-link {{ request()->routeIs('admin.announcements.index') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-speakerphone"></i></span>
        <span class="menu-text">Comunicados</span>
    </a>
</li>
@endhasanyrole {{-- end PROVEEDORES --}}

{{-- ═══════════════════════════════════════════════════
     CONFIGURACIÓN — visible to: superadmin only
     ═══════════════════════════════════════════════════ --}}
@hasrole('superadmin')
<li class="side-nav-title">CONFIGURACIÓN</li>

{{-- Usuarios Staff — visible to: superadmin --}}
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('users.staff.index') ? 'active' : '' }}"
        href="{{ route('users.staff.index') }}">
        <span class="menu-icon"><i class="ti ti-users"></i></span>
        <span class="menu-text">Usuarios Staff</span>
    </a>
</li>

{{-- Empleados — visible to: superadmin --}}
<li class="side-nav-item">
    <a class="side-nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}"
        href="{{ route('employees.index') }}">
        <span class="menu-icon"><i class="ti ti-id-badge"></i></span>
        <span class="menu-text">Empleados</span>
    </a>
</li>

{{-- Catálogos del sistema accordion — visible to: superadmin --}}
@php
$openConfiguration =
    request()->routeIs('companies.*') ||
    request()->routeIs('stations.*') ||
    request()->routeIs('departments.*') ||
    request()->routeIs('receiving-locations.*') ||
    request()->routeIs('taxes.*') ||
    request()->routeIs('approval-levels.*') ||
    request()->routeIs('sat-retenciones.*');
@endphp
<li class="side-nav-item">
    <a class="side-nav-link {{ $openConfiguration ? '' : 'collapsed' }}" data-bs-toggle="collapse"
        href="#sidebarConfigurations" role="button"
        aria-expanded="{{ $openConfiguration ? 'true' : 'false' }}"
        aria-controls="sidebarConfigurations">
        <span class="menu-icon"><i class="ti ti-settings"></i></span>
        <span class="menu-text">Catálogos</span>
        <span class="menu-arrow"></span>
    </a>
    <div class="{{ $openConfiguration ? 'show' : '' }} collapse" id="sidebarConfigurations">
        <ul class="sub-menu">
            <li class="side-nav-item">
                <a href="{{ route('companies.index') }}"
                    class="side-nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <span class="menu-text">Empresas</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('stations.index') }}"
                    class="side-nav-link {{ request()->routeIs('stations.*') ? 'active' : '' }}">
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
                <a href="{{ route('receiving-locations.index') }}"
                    class="side-nav-link {{ request()->routeIs('receiving-locations.*') ? 'active' : '' }}">
                    <span class="menu-text">Ubicaciones de Recepción</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('taxes.index') }}"
                    class="side-nav-link {{ request()->routeIs('taxes.*') ? 'active' : '' }}">
                    <span class="menu-text">IVA</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('approval-levels.index') }}"
                    class="side-nav-link {{ request()->routeIs('approval-levels.*') ? 'active' : '' }}">
                    <span class="menu-text">Niveles de Autorización</span>
                    <span class="badge bg-soft-danger text-danger ms-auto">🛡️</span>
                </a>
            </li>
            <li class="side-nav-item">
                <a href="{{ route('sat-retenciones.index') }}"
                    class="side-nav-link {{ request()->routeIs('sat-retenciones.*') ? 'active' : '' }}">
                    <span class="menu-text">Retenciones SAT</span>
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- Incidentes Reportados — visible to: superadmin --}}
<li class="side-nav-item text-danger">
    <a href="{{ route('incidents.index') }}"
        class="side-nav-link text-danger {{ request()->routeIs('incidents.*') ? 'active' : '' }}">
        <span class="menu-icon"><i class="ti ti-bug"></i></span>
        <span class="menu-text">Incidentes Reportados</span>
    </a>
</li>
@endhasrole {{-- end CONFIGURACIÓN --}}
