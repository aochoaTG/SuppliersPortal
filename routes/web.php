<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    ProfilePasswordController,
    LockScreenController,
    UserController,
    IncidentController,
    AnnouncementController,
    SupplierController,
    SupplierDocumentController,
    SupplierBankController,
    SupplierSirocController,
    DocumentReviewController,
    CatSupplierController,
    SatEfos69bController,
    CompanyController,
    TaxController,
    StationController,
    CategoryController,
    CostCenterController,
    AnnualBudgetController,
    RequisitionController,
    DepartmentController,
    BudgetMovementController,
    RequisitionWorkflowController
};

// ============================================================================
//  Información del entorno / Default redirect
// ============================================================================
Route::get('/check-user', function () {
    return [
        'process_user' => get_current_user(),
        'process_id' => getmypid(),
        'web_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    ];
});

Route::get('/', fn() => redirect()->route('login'));

// ============================================================================
//  Lock Screen (solo requiere autenticación, no lock)
// ============================================================================
Route::middleware(['auth'])->group(function () {
    Route::post('/lock', [LockScreenController::class, 'lock'])->name('lockscreen.lock');
    Route::get('/lock', [LockScreenController::class, 'show'])->name('lockscreen.show');
    Route::post('/unlock', [LockScreenController::class, 'unlock'])->name('lockscreen.unlock');
});

// ============================================================================
//  Panel protegido (auth + lock)
// ============================================================================
Route::middleware(['auth', 'lock'])->group(function () {

    // ------------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------------
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // ------------------------------------------------------------------------
    // Incidents
    // ------------------------------------------------------------------------
    Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
    Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');

    // ------------------------------------------------------------------------
    // Users - Staff
    // ------------------------------------------------------------------------
    Route::get('/users', [UserController::class, 'index'])->name('users.staff.index');
    Route::get('/users/datatable', [UserController::class, 'datatable'])->name('users.datatable');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    // ------------------------------------------------------------------------
    // Users - Suppliers (debe ir antes del {user})
    // ------------------------------------------------------------------------
    Route::get('/users/suppliers', [UserController::class, 'SupplierIndex'])->name('users.suppliers.index');
    Route::get('/users/suppliers/datatable', [UserController::class, 'suppliersDatatable'])->name('users.suppliers.datatable');
    Route::get('/users/suppliers/{user}/edit', [UserController::class, 'editSupplier'])->name('users.suppliers.edit');
    Route::patch('/users/suppliers/{user}/toggle', [UserController::class, 'toggleSupplier'])->name('users.suppliers.toggle');
    Route::delete('/users/suppliers/{user}', [UserController::class, 'destroySupplier'])->name('users.suppliers.destroy');
    Route::put('/users/suppliers/{user}', [UserController::class, 'updateSupplier'])->name('users.suppliers.update');

    // ------------------------------------------------------------------------
    // Users - Staff (con parámetros)
    // ------------------------------------------------------------------------
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.staff.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/roles/edit', [UserController::class, 'editRoles'])->name('users.roles.edit');
    Route::patch('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
    // Users - Companies (asignación por usuario)
    Route::get('/users/{user}/companies/edit', [UserController::class, 'editCompanies'])->name('users.companies.edit');
    Route::patch('/users/{user}/companies', [UserController::class, 'updateCompanies'])->name('users.companies.update');

    // ------------------------------------------------------------------------
    // Profile
    // ------------------------------------------------------------------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/change-password', [ProfilePasswordController::class, 'update'])->name('profile.password.update');

    // ========================================================================
    //  ADMIN: Comunicados (Announcements)
    // ========================================================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('announcements', [AnnouncementController::class, 'adminIndex'])->name('announcements.index');
        Route::get('announcements/datatable', [AnnouncementController::class, 'adminDatatable'])->name('announcements.datatable');
        Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    // ========================================================================
    //  SUPPLIER: Comunicados
    // ========================================================================
    Route::prefix('supplier')->name('supplier.')->group(function () {
        Route::get('announcements', [AnnouncementController::class, 'inbox'])->name('announcements.inbox');
        Route::get('announcements/datatable', [AnnouncementController::class, 'supplierDatatable'])->name('announcements.datatable');
        Route::get('announcements/{announcement}/pdf', [AnnouncementController::class, 'pdf'])->name('announcements.pdf');
        Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
        Route::post('announcements/{announcement}/view', [AnnouncementController::class, 'markViewed'])->name('announcements.view');
        Route::post('announcements/{announcement}/dismiss', [AnnouncementController::class, 'dismiss'])->name('announcements.dismiss');
    });

    // ========================================================================
    //  SUPPLIER: Documentos
    // ========================================================================
    Route::middleware(['auth'])->group(function () {
        Route::get('documents', [SupplierDocumentController::class, 'index'])->name('documents.suppliers.index');
        Route::post('documents/{supplier}', [SupplierDocumentController::class, 'store'])->name('documents.suppliers.store');
        Route::post('documents/{supplier}/{document}/review', [SupplierDocumentController::class, 'review'])->name('documents.suppliers.review');
        Route::delete('suppliers/{supplier}/documents/{document}', [SupplierDocumentController::class, 'destroy'])->name('suppliers.documents.destroy');
        Route::post('/documents/review/feedback', [SupplierDocumentController::class, 'feedback'])->name('documents.suppliers.feedback');
    });

    Route::middleware(['auth'])
        ->prefix('supplier/documents')
        ->name('supplier.documents.')
        ->group(function () {
            Route::get('/', [SupplierDocumentController::class, 'index'])->name('index');
            Route::post('/{supplier}', [SupplierDocumentController::class, 'store'])->name('store');
            Route::delete('/{supplier}/{document}', [SupplierDocumentController::class, 'destroy'])->name('destroy');
            Route::post('/{supplier}/{document}/review', [SupplierDocumentController::class, 'review'])->name('review');
        });

    // ========================================================================
    //  ADMIN: Revisión de documentos
    // ========================================================================
    Route::middleware(['auth'])
        ->prefix('admin/review')
        ->name('admin.review.')
        ->group(function () {
            Route::get('/', [DocumentReviewController::class, 'index'])->name('index');
            Route::get('/queue', [DocumentReviewController::class, 'queue'])->name('queue');
            Route::get('/suppliers', [DocumentReviewController::class, 'suppliers'])->name('suppliers');
            Route::get('/suppliers/{supplier}', [DocumentReviewController::class, 'showSupplier'])->name('suppliers.show');

            Route::post('/documents/{document}/accept', [DocumentReviewController::class, 'accept'])->name('documents.accept');
            Route::post('/documents/{document}/reject', [DocumentReviewController::class, 'reject'])->name('documents.reject');
        });

    // ========================================================================
    //  SUPPLIER: Bancos / REPSE
    // ========================================================================
    Route::patch('/suppliers/{supplier}/bank', [SupplierBankController::class, 'update'])->name('suppliers.bank.update');
    Route::delete('/suppliers/{supplier}/bank', [SupplierBankController::class, 'destroy'])->name('suppliers.bank.destroy');
    Route::patch('/suppliers/{supplier}/repse', [SupplierBankController::class, 'updateRepse'])->name('suppliers.repse.update');

    // ========================================================================
    //  SAT EFOS 69-B
    // ========================================================================
    Route::get('sat-efos-69b', [SatEfos69bController::class, 'index'])->name('sat_efos_69b.index');
    Route::get('sat-efos-69b/data', [SatEfos69bController::class, 'data'])->name('sat_efos_69b.data');

    // ========================================================================
    //  Catálogo de Proveedores
    // ========================================================================
    Route::prefix('cat-suppliers')->name('cat-suppliers.')->group(function () {
        Route::get('/', [CatSupplierController::class, 'index'])->name('index');
        Route::get('/{catSupplier}/edit', [CatSupplierController::class, 'edit'])->name('edit');
        Route::put('/{catSupplier}', [CatSupplierController::class, 'update'])->name('update');
        Route::get('/datatable', [CatSupplierController::class, 'datatable'])->name('datatable');
    });

    // ========================================================================
    //  SIROC (por proveedor y global)
    // ========================================================================
    Route::get('/sirocs', [SupplierSirocController::class, 'adminIndex'])->name('sirocs.index');
    Route::get('/suppliers/{supplier}/sirocs', [SupplierSirocController::class, 'index'])->name('suppliers.sirocs.index');
    Route::get('/suppliers/{supplier}/sirocs/create', [SupplierSirocController::class, 'create'])->name('suppliers.sirocs.create');
    Route::post('/suppliers/{supplier}/sirocs', [SupplierSirocController::class, 'store'])->name('suppliers.sirocs.store');
    Route::get('/suppliers/{supplier}/sirocs/{siroc}', [SupplierSirocController::class, 'show'])->name('suppliers.sirocs.show');
    Route::get('/suppliers/{supplier}/sirocs/{siroc}/edit', [SupplierSirocController::class, 'edit'])->name('suppliers.sirocs.edit');
    Route::put('/suppliers/{supplier}/sirocs/{siroc}', [SupplierSirocController::class, 'update'])->name('suppliers.sirocs.update');
    Route::delete('/suppliers/{supplier}/sirocs/{siroc}', [SupplierSirocController::class, 'destroy'])->name('suppliers.sirocs.destroy');

    // ========================================================================
    //  Supplier <-> User relación directa
    // ========================================================================
    Route::get('/users/{user}/supplier/edit', [SupplierController::class, 'edit'])->name('users.supplier.edit');
    Route::post('/users/{user}/supplier', [SupplierController::class, 'store'])->name('users.supplier.store');
    Route::put('/users/{user}/supplier', [SupplierController::class, 'update'])->name('users.supplier.update');
    Route::get('/api/suppliers/search', [SupplierController::class, 'search'])->name('api.suppliers.search');

    // ========================================================================
    //  Companies (Multiempresa)
    // ========================================================================
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/datatable', [CompanyController::class, 'datatable'])->name('datatable');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
    });

    // ========================================================================
    //  Taxes
    // ========================================================================
    Route::resource('taxes', TaxController::class)->except(['show']);

    // ========================================================================
    //  Stations
    // ========================================================================
    Route::post('stations/{station}/toggle-active', [StationController::class, 'toggleActive'])->name('stations.toggle-active');
    Route::resource('stations', StationController::class);
    Route::get('stations-datatable', [StationController::class, 'datatable'])->name('stations.datatable');
    Route::post('stations/{station}/link-company', [StationController::class, 'linkCompany'])->name('stations.link-company');


    // ========================================================================
    //  Categories de Centros de costos
    // ========================================================================
    // Recurso REST para categorías.
    Route::get('categories/datatable', [CategoryController::class, 'datatable'])->name('categories.datatable');
    Route::resource('categories', CategoryController::class)->parameters(['categories' => 'category']); // Para route model binding claro


    // ========================================================================
    //  Centros de costos
    // ========================================================================
    Route::get('cost-centers/datatable', [CostCenterController::class, 'datatable'])->name('cost-centers.datatable');
    Route::get('/api/companies/{company}/cost-centers', [CostCenterController::class, 'byCompany'])->name('api.cost-centers.by-company');
    Route::resource('cost-centers', CostCenterController::class)->parameters(['cost-centers' => 'cost_center']);


    // ========================================================================
    //  Presupuestos Anuales
    // ========================================================================
    Route::get('annual-budgets/datatable', [AnnualBudgetController::class, 'datatable'])->name('annual-budgets.datatable');
    Route::resource('annual-budgets', AnnualBudgetController::class)->parameters(['annual-budgets' => 'annual_budget']);

    // ========================================================================
//  Requisiciones
// ========================================================================
    Route::get('requisitions/datatable', [RequisitionController::class, 'datatable'])->name('requisitions.datatable');

    // Bandejas
    Route::get('requisitions/inbox/review', [RequisitionWorkflowController::class, 'reviewInbox'])->name('requisitions.inbox.review');
    Route::get('requisitions/inbox/approval', [RequisitionWorkflowController::class, 'approvalInbox'])->name('requisitions.inbox.approval');
    Route::get('requisitions/inbox/rejected', [RequisitionWorkflowController::class, 'rejectedInbox'])->name('requisitions.inbox.rejected');

    // Transiciones nuevas
    Route::post('requisitions/{requisition}/submit', [RequisitionWorkflowController::class, 'submit'])->name('requisitions.submit');
    Route::post('requisitions/{requisition}/mark-reviewed', [RequisitionWorkflowController::class, 'markReviewed'])->name('requisitions.mark-reviewed');
    Route::post('requisitions/{requisition}/hold', [RequisitionWorkflowController::class, 'hold'])->name('requisitions.hold');

    // Transiciones (existentes)
    Route::post('requisitions/{requisition}/approve', [RequisitionWorkflowController::class, 'approve'])->name('requisitions.approve');
    Route::post('requisitions/{requisition}/cancel', [RequisitionWorkflowController::class, 'cancel'])->name('requisitions.cancel');
    Route::post('requisitions/{requisition}/reject', [RequisitionWorkflowController::class, 'reject'])->name('requisitions.reject');
    Route::post('requisitions/{requisition}/consume', [RequisitionWorkflowController::class, 'consume'])->name('requisitions.consume');

    // Snapshot presupuestal
    Route::get('/budget/snapshot', [BudgetMovementController::class, 'snapshot'])->name('budget.snapshot');

    // CRUD principal
    Route::resource('requisitions', RequisitionController::class);

    // ========================================================================
    //  Departamentos
    // ========================================================================
    Route::resource('departments', DepartmentController::class)->except(['show']);

    // ========================================================================
    //  Movimientos presupuestales
    // ========================================================================
    Route::resource('budget-movements', BudgetMovementController::class)->only(['index', 'show']);


});


// ============================================================================
//  Autenticación
// ============================================================================
require __DIR__ . '/auth.php';
// ============================================================================

