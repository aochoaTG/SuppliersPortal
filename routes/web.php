<?php

use App\Models\Requisition;
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
    RequisitionWorkflowController,
    ProductServiceController,
    BudgetMonthlyDistributionController,
    ExpenseCategoryController,
    QuotationPlannerController,
    RfqController,
    SupplierPortalController,
    RfqInboxController,
    RfqComparisonController,
    ApprovalLevelController,
    QuotationApprovalController,
    PurchaseOrderController,
    DirectPurchaseOrderController,
};

// ============================================================================
//  Default redirect
// ============================================================================
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
    //  Dashboard
    // ------------------------------------------------------------------------
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // ------------------------------------------------------------------------
    //  Profile & Account
    // ------------------------------------------------------------------------
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::post('/change-password', [ProfilePasswordController::class, 'update'])->name('password.update');
    });

    // ------------------------------------------------------------------------
    //  Incidents
    // ------------------------------------------------------------------------
    Route::prefix('incidents')->name('incidents.')->group(function () {
        Route::get('/', [IncidentController::class, 'index'])->name('index');
        Route::post('/', [IncidentController::class, 'store'])->name('store');
        Route::delete('/{incident}', [IncidentController::class, 'destroy'])->name('destroy');
    });

    // ========================================================================
    //  User Management
    // ========================================================================
    Route::prefix('users')->name('users.')->group(function () {
        // Staff
        Route::get('/', [UserController::class, 'index'])->name('staff.index');
        Route::get('/datatable', [UserController::class, 'datatable'])->name('datatable');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');

        // Suppliers
        Route::get('/suppliers', [UserController::class, 'SupplierIndex'])->name('suppliers.index');
        Route::get('/suppliers/datatable', [UserController::class, 'suppliersDatatable'])->name('suppliers.datatable');
        Route::get('/suppliers/{user}/edit', [UserController::class, 'editSupplier'])->name('suppliers.edit');
        Route::patch('/suppliers/{user}/toggle', [UserController::class, 'toggleSupplier'])->name('suppliers.toggle');
        Route::delete('/suppliers/{user}', [UserController::class, 'destroySupplier'])->name('suppliers.destroy');
        Route::put('/suppliers/{user}', [UserController::class, 'updateSupplier'])->name('suppliers.update');

        // Staff (rutas con parámetros)
        Route::get('/{user}', [UserController::class, 'show'])->name('staff.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::get('/{user}/roles/edit', [UserController::class, 'editRoles'])->name('roles.edit');
        Route::patch('/{user}/roles', [UserController::class, 'updateRoles'])->name('roles.update');

        // Companies (asignación por usuario)
        Route::get('/{user}/companies/edit', [UserController::class, 'editCompanies'])->name('companies.edit');
        Route::patch('/{user}/companies', [UserController::class, 'updateCompanies'])->name('companies.update');

        // Cost Centers (asignación por usuario)
        Route::get('/{user}/cost-centers/edit', [UserController::class, 'editCostCenters'])->name('cost-centers.edit');
        Route::patch('/{user}/cost-centers', [UserController::class, 'updateCostCenters'])->name('cost-centers.update');

        // Supplier relación directa
        Route::get('/{user}/supplier/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
        Route::post('/{user}/supplier', [SupplierController::class, 'store'])->name('supplier.store');
        Route::put('/{user}/supplier', [SupplierController::class, 'update'])->name('supplier.update');
    });

    // ========================================================================
    //  Admin: Announcements & Document Review
    // ========================================================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'adminIndex'])->name('index');
            Route::get('/datatable', [AnnouncementController::class, 'adminDatatable'])->name('datatable');
            Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
            Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
            Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('review')->name('review.')->group(function () {
            Route::get('/', [DocumentReviewController::class, 'index'])->name('index');
            Route::get('/queue', [DocumentReviewController::class, 'queue'])->name('queue');
            Route::get('/suppliers', [DocumentReviewController::class, 'suppliers'])->name('suppliers');
            Route::get('/suppliers/{supplier}', [DocumentReviewController::class, 'showSupplier'])->name('suppliers.show');
            Route::post('/documents/{document}/accept', [DocumentReviewController::class, 'accept'])->name('documents.accept');
            Route::post('/documents/{document}/reject', [DocumentReviewController::class, 'reject'])->name('documents.reject');
        });
    });

    // ========================================================================
    //  Supplier Documents (rutas globales)
    // ========================================================================
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [SupplierDocumentController::class, 'index'])->name('suppliers.index');
        Route::post('/{supplier}', [SupplierDocumentController::class, 'store'])->name('suppliers.store');
        Route::post('/{supplier}/{document}/review', [SupplierDocumentController::class, 'review'])->name('suppliers.review');
        Route::delete('/suppliers/{supplier}/documents/{document}', [SupplierDocumentController::class, 'destroy'])->name('suppliers.destroy');
        Route::post('/review/feedback', [SupplierDocumentController::class, 'feedback'])->name('suppliers.feedback');
    });

    // ========================================================================
    //  Supplier Banking, REPSE & SIROC
    // ========================================================================
    Route::prefix('suppliers/{supplier}')->name('suppliers.')->group(function () {
        Route::patch('/bank', [SupplierBankController::class, 'update'])->name('bank.update');
        Route::delete('/bank', [SupplierBankController::class, 'destroy'])->name('bank.destroy');
        Route::patch('/repse', [SupplierBankController::class, 'updateRepse'])->name('repse.update');

        Route::prefix('sirocs')->name('sirocs.')->group(function () {
            Route::get('/', [SupplierSirocController::class, 'index'])->name('index');
            Route::get('/create', [SupplierSirocController::class, 'create'])->name('create');
            Route::post('/', [SupplierSirocController::class, 'store'])->name('store');
            Route::get('/{siroc}', [SupplierSirocController::class, 'show'])->name('show');
            Route::get('/{siroc}/edit', [SupplierSirocController::class, 'edit'])->name('edit');
            Route::put('/{siroc}', [SupplierSirocController::class, 'update'])->name('update');
            Route::delete('/{siroc}', [SupplierSirocController::class, 'destroy'])->name('destroy');
        });
    });

    Route::get('/sirocs', [SupplierSirocController::class, 'adminIndex'])->name('sirocs.index');

    // ========================================================================
    //  SAT & Catalog Management
    // ========================================================================
    Route::prefix('sat-efos-69b')->name('sat_efos_69b.')->group(function () {
        Route::get('/', [SatEfos69bController::class, 'index'])->name('index');
        Route::get('/data', [SatEfos69bController::class, 'data'])->name('data');
    });

    Route::prefix('cat-suppliers')->name('cat-suppliers.')->group(function () {
        Route::get('/', [CatSupplierController::class, 'index'])->name('index');
        Route::get('/datatable', [CatSupplierController::class, 'datatable'])->name('datatable');
        Route::get('/{catSupplier}/edit', [CatSupplierController::class, 'edit'])->name('edit');
        Route::put('/{catSupplier}', [CatSupplierController::class, 'update'])->name('update');
    });

    // ========================================================================
    //  Catalogs (Route::resource)
    // ========================================================================
    Route::get('companies/datatable', [CompanyController::class, 'datatable'])->name('companies.datatable');
    Route::resource('companies', CompanyController::class)->except(['show']);

    Route::get('stations/datatable', [StationController::class, 'datatable'])->name('stations.datatable');
    Route::resource('stations', StationController::class)->except(['show']);
    Route::post('stations/{station}/toggle-active', [StationController::class, 'toggleActive'])->name('stations.toggle-active');
    Route::post('stations/{station}/link-company', [StationController::class, 'linkCompany'])->name('stations.link-company');

    Route::resource('taxes', TaxController::class)->except(['show']);

    Route::get('categories/datatable', [CategoryController::class, 'datatable'])->name('categories.datatable');
    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::get('cost-centers/datatable', [CostCenterController::class, 'datatable'])->name('cost-centers.datatable');
    Route::get('cost-centers/api/companies/{company}/cost-centers', [CostCenterController::class, 'byCompany'])->name('cost-centers.api.by-company');
    Route::resource('cost-centers', CostCenterController::class)->except(['show'])->parameters(['cost-centers' => 'cost_center']);

    Route::resource('departments', DepartmentController::class)->except(['show']);

    // ========================================================================
    //  Annual Budgets
    // ========================================================================
    Route::prefix('annual_budgets')->name('annual_budgets.')->group(function () {
        Route::get('/', [AnnualBudgetController::class, 'index'])->name('index');
        Route::get('/datatable', [AnnualBudgetController::class, 'datatable'])->name('datatable');
        Route::get('/create', [AnnualBudgetController::class, 'create'])->name('create');
        Route::post('/', [AnnualBudgetController::class, 'store'])->name('store');
        Route::get('/{annual_budget}', [AnnualBudgetController::class, 'show'])->name('show');
        Route::get('/{annual_budget}/edit', [AnnualBudgetController::class, 'edit'])->name('edit');
        Route::put('/{annual_budget}', [AnnualBudgetController::class, 'update'])->name('update');
        Route::delete('/{annual_budget}', [AnnualBudgetController::class, 'destroy'])->name('destroy');
        Route::get('/{annual_budget}/approve', [AnnualBudgetController::class, 'approve'])->name('approve');
        Route::post('/{annual_budget}/approve', [AnnualBudgetController::class, 'approveStore'])->name('approve.store');

        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/available-categories-month', [AnnualBudgetController::class, 'getAvailableCategoriesForMonth'])
                ->name('available-categories-month');
            Route::get('/check-availability', [AnnualBudgetController::class, 'checkAvailability'])
                ->name('check-availability');
        });
    });

    // ========================================================================
    //  Monthly Budget Distributions
    // ========================================================================
    Route::prefix('budget_monthly_distributions')->name('budget_monthly_distributions.')->group(function () {
        Route::get('/', [BudgetMonthlyDistributionController::class, 'index'])->name('index');
        Route::get('/datatable', [BudgetMonthlyDistributionController::class, 'datatable'])->name('datatable');
        Route::get('/{annual_budget}/create', [BudgetMonthlyDistributionController::class, 'create'])->name('create');
        Route::post('/', [BudgetMonthlyDistributionController::class, 'store'])->name('store');
        Route::get('/{annual_budget}/matrix', [BudgetMonthlyDistributionController::class, 'matrix'])->name('matrix');
        Route::get('/{budget_monthly_distribution}', [BudgetMonthlyDistributionController::class, 'show'])->name('show');
        Route::get('/{annual_budget}/edit', [BudgetMonthlyDistributionController::class, 'edit'])->name('edit');
        Route::put('/{annual_budget}', [BudgetMonthlyDistributionController::class, 'update'])->name('update');
    });

    // ========================================================================
    //  Budget Movements
    // ========================================================================
    Route::get('budget_movements/dashboard/critical', [BudgetMovementController::class, 'criticalDashboard'])->name('budget_movements.dashboard');
    Route::get('budget_movements/check-budget/availability', [BudgetMovementController::class, 'checkBudgetAvailability'])->name('budget_movements.check_budget');
    Route::resource('budget_movements', BudgetMovementController::class)->parameters(['budget_movements' => 'budgetMovement']);
    Route::post('budget_movements/{budgetMovement}/approve', [BudgetMovementController::class, 'approve'])->name('budget_movements.approve');
    Route::post('budget_movements/{budgetMovement}/reject', [BudgetMovementController::class, 'reject'])->name('budget_movements.reject');

    // ========================================================================
    //  Requisitions CRUD
    // ========================================================================
    Route::controller(RequisitionController::class)->prefix('requisitions')->name('requisitions.')->group(function () {
        // DataTables (AJAX) - deben ir antes de rutas con parámetros
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/approval_datatable', 'approvalDatatable')->name('approval_datatable');

        // Flujo de Compras (AJAX para Modales)
        Route::get('/{requisition}/review-data', 'reviewData')->name('review-data');
        Route::post('/{requisition}/validate-technical', 'validateTechnical')->name('validate-technical');
        Route::post('/{requisition}/reject', 'reject')->name('reject');

        // CRUD
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/create-livewire', 'createLivewire')->name('create-livewire');
        Route::get('/{requisition}/edit-livewire', 'editLivewire')->name('edit-livewire');
        Route::post('/', 'store')->name('store');
        Route::get('/{requisition}', 'show')->name('show');
        Route::get('/{requisition}/edit', 'edit')->name('edit');
        Route::put('/{requisition}', 'update')->name('update');
        Route::delete('/{requisition}', 'destroy')->name('destroy');
        Route::put('/{requisition}/cancel', 'cancel')->name('cancel');
    });

    // ========================================================================
    //  Requisitions Workflow
    // ========================================================================
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        // Bandejas de workflow
        Route::get('/inbox/validation', [RequisitionWorkflowController::class, 'validationInbox'])->name('inbox.validation');
        Route::get('/inbox/rejected', [RequisitionWorkflowController::class, 'rejectedInbox'])->name('inbox.rejected');

        // Vista de revisión/validación
        Route::get('/{requisition}/validate', [RequisitionWorkflowController::class, 'showValidationPage'])->name('validate.show');

        // Transiciones de workflow
        Route::post('/{requisition}/submit', [RequisitionWorkflowController::class, 'submit'])->name('submit');
        Route::post('/{requisition}/hold', [RequisitionWorkflowController::class, 'hold'])->name('hold');
        Route::post('/{requisition}/validate', [RequisitionWorkflowController::class, 'validate'])->name('validate');
        Route::post('/{requisition}/cancel', [RequisitionWorkflowController::class, 'cancel'])->name('workflow.cancel');
        Route::post('/{requisition}/reject', [RequisitionWorkflowController::class, 'reject'])->name('workflow.reject');
        Route::post('/{requisition}/consume', [RequisitionWorkflowController::class, 'consume'])->name('consume');
    });

    // ========================================================================
    //  Quotation Planner
    // ========================================================================
    Route::prefix('requisitions/{requisition}/quotation-planner')
        ->name('requisitions.quotation-planner.')
        ->group(function () {
            Route::get('/', [QuotationPlannerController::class, 'show'])->name('show');
            Route::post('/save', [QuotationPlannerController::class, 'saveStrategy'])->name('save');
            Route::post('/groups', [QuotationPlannerController::class, 'createGroup'])->name('groups.create');
            Route::delete('/groups/{group}', [QuotationPlannerController::class, 'deleteGroup'])->name('groups.delete');
            Route::post('/groups/{group}/items', [QuotationPlannerController::class, 'addItemsToGroup'])->name('groups.items.add');
            Route::delete('/groups/{group}/items', [QuotationPlannerController::class, 'removeItemsFromGroup'])->name('groups.items.remove');
            Route::get('/groups/{group}/suggestions', [QuotationPlannerController::class, 'getSupplierSuggestions'])->name('groups.suggestions');
        });

    // ========================================================================
    //  RFQ (Request for Quotation)
    // ========================================================================
    Route::prefix('rfq')->name('rfq.')->controller(RfqController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/wizard/{requisition}/summary', 'wizardSummary')->name('wizard.summary');
        Route::get('/summary', 'summary')->name('summary');
        Route::get('/inbox/received', 'receivedInbox')->name('inbox.received');

        Route::get('/select-suppliers/{requisition}', 'selectSuppliers')->name('select-suppliers');
        Route::post('/create/{requisition}', 'createRFQs')->name('create');
        Route::get('/compare/{requisition}', 'compare')->name('compare');
        Route::post('/approve/{requisition}', 'approve')->name('approve');

        Route::post('/{rfq}/send-single', 'sendSingle')->name('send.single');
        Route::post('/{rfq}/send', 'send')->name('send');
        Route::get('/{rfq}', 'show')->name('show')->where('rfq', '[0-9]+');
        Route::post('/{rfq}/cancel', 'cancelRFQ')->name('cancel');
    });

    // RFQ Inbox & Analysis
    Route::prefix('rfq')->name('rfq.')->group(function () {
        Route::get('/wizard/{requisition}/analysis-data', [RfqInboxController::class, 'analysisData'])->name('wizard.analysis.data');

        Route::prefix('inbox')->name('inbox.')->group(function () {
            Route::get('/pending', [RfqInboxController::class, 'pending'])->name('pending');
            Route::get('/pending/data', [RfqInboxController::class, 'pendingData'])->name('pending.data');
            Route::get('/modal-rfq/{rfq}', [RfqInboxController::class, 'rfqModalContent'])->name('modal.rfq');
            Route::get('/modal-req/{requisition}', [RfqInboxController::class, 'reqModalContent'])->name('modal.req');
        });
    });

    // RFQ Comparison
    Route::get('rfq/{rfq}/comparison', [RfqComparisonController::class, 'index'])->name('rfq.comparison.index');

    // RFQ Manage & Wizard
    Route::get('/rfq/manage', fn() => view('rfq.manage'))->name('quotes.index');
    Route::get('/rfq/wizard/{requisition}', function (Requisition $requisition) {
        return view('rfq.wizard', compact('requisition'));
    })->name('rfq.wizard.steps');

    // ========================================================================
    //  Products & Services Catalog
    // ========================================================================
    Route::prefix('products-services')->name('products-services.')->group(function () {
        Route::get('/', [ProductServiceController::class, 'index'])->name('index');
        Route::get('/datatable', [ProductServiceController::class, 'datatable'])->name('datatable');
        Route::get('/create', [ProductServiceController::class, 'create'])->name('create');
        Route::post('/', [ProductServiceController::class, 'store'])->name('store');
        Route::get('/{productService}', [ProductServiceController::class, 'show'])->name('show');
        Route::get('/{productService}/edit', [ProductServiceController::class, 'edit'])->name('edit');
        Route::put('/{productService}', [ProductServiceController::class, 'update'])->name('update');
        Route::delete('/{productService}', [ProductServiceController::class, 'destroy'])->name('destroy');

        Route::post('/{productService}/approve', [ProductServiceController::class, 'approve'])->name('approve');
        Route::post('/{productService}/reject', [ProductServiceController::class, 'reject'])->name('reject');
        Route::post('/{productService}/deactivate', [ProductServiceController::class, 'deactivate'])->name('deactivate');
        Route::post('/{productService}/reactivate', [ProductServiceController::class, 'reactivate'])->name('reactivate');

        Route::get('/api/active', [ProductServiceController::class, 'apiActive'])->name('api.active');
        Route::post('/from-requisition', [ProductServiceController::class, 'storeFromRequisition'])->name('store-from-requisition');
        Route::get('/api/active-for-requisitions', [ProductServiceController::class, 'apiActiveForRequisitions'])->name('api.active-for-requisitions');
    });

    // ========================================================================
    //  API Routes (internal)
    // ========================================================================
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('suppliers.search');

        Route::get('/annual_budgets/{annual_budget}/distributions', [BudgetMonthlyDistributionController::class, 'getByBudget'])
            ->name('annual_budgets.distributions');
        Route::get('/annual_budgets/{annual_budget}/check-availability/{month}/{categoryId}', [BudgetMonthlyDistributionController::class, 'checkAvailability'])
            ->name('annual_budgets.check-availability');

        Route::get('/budget/check-availability', [ExpenseCategoryController::class, 'checkBudget'])->name('budget.check-availability');
    });

    // Expense Categories
    Route::prefix('expense-categories')->name('expense-categories.')->group(function () {
        Route::post('/', [ExpenseCategoryController::class, 'store'])->name('store');
        Route::get('/select', [ExpenseCategoryController::class, 'getForSelect'])->name('select');
        Route::get('/by-budget', [ExpenseCategoryController::class, 'byBudget'])->name('by-budget');
        Route::get('/by-cost-center', [ExpenseCategoryController::class, 'getByCostCenter'])->name('by-cost-center');
    });

}); // Fin del grupo auth + lock

// ============================================================================
//  Supplier Portal (auth + role:supplier) - UNIFICADO
// ============================================================================
Route::middleware(['auth', 'role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SupplierPortalController::class, 'dashboard'])->name('dashboard');

    // Announcements
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'inbox'])->name('inbox');
        Route::get('/datatable', [AnnouncementController::class, 'supplierDatatable'])->name('datatable');
        Route::get('/{announcement}/pdf', [AnnouncementController::class, 'pdf'])->name('pdf');
        Route::get('/{announcement}', [AnnouncementController::class, 'show'])->name('show');
        Route::post('/{announcement}/view', [AnnouncementController::class, 'markViewed'])->name('view');
        Route::post('/{announcement}/dismiss', [AnnouncementController::class, 'dismiss'])->name('dismiss');
    });

    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [SupplierDocumentController::class, 'index'])->name('index');
        Route::post('/{supplier}', [SupplierDocumentController::class, 'store'])->name('store');
        Route::delete('/{supplier}/{document}', [SupplierDocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{supplier}/{document}/review', [SupplierDocumentController::class, 'review'])->name('review');
    });

    // RFQ
    Route::get('/rfq/{rfq}', [SupplierPortalController::class, 'showRfq'])->name('rfq.show');
    Route::post('/rfq/{rfq}/quotation', [SupplierPortalController::class, 'saveQuotation'])->name('rfq.quotation.save');

    // Quotation History
    Route::get('/quotations/history', [SupplierPortalController::class, 'quotationHistory'])->name('quotations.history');

    // Download attachment
    Route::get('/quotation/{response}/download', [SupplierPortalController::class, 'downloadAttachment'])->name('quotation.download');

    // Delete draft
    Route::delete('/quotation/{response}/draft', [SupplierPortalController::class, 'deleteDraft'])->name('quotation.draft.delete');
});

// ============================================================================
//  Approval Levels & Quotation Approvals (superadmin)
// ============================================================================
Route::middleware(['auth', 'lock', 'role:superadmin'])->group(function () {
    Route::resource('approval-levels', ApprovalLevelController::class)
        ->only(['index', 'edit', 'update'])
        ->names('approval-levels');

    Route::get('/approvals/quotations', [QuotationApprovalController::class, 'index'])->name('approvals.quotations.index');
    Route::post('/approvals/quotations/{summary}/handle', [QuotationApprovalController::class, 'handle'])->name('approvals.quotations.handle');
});

// ============================================================================
//  Purchase Orders & RFQ Selection (superadmin | buyer)
// ============================================================================
Route::middleware(['auth', 'lock', 'role:superadmin|buyer'])->group(function () {
    // RFQ Selection
    Route::post('/rfq/{rfq}/select', [RfqComparisonController::class, 'select'])->name('rfq.comparison.select');

    // Direct Purchase Orders
    Route::get('/direct-purchase-orders/create', [DirectPurchaseOrderController::class, 'create'])->name('direct-purchase-orders.create');
    Route::post('/direct-purchase-orders', [DirectPurchaseOrderController::class, 'store'])->name('direct-purchase-orders.store');
    Route::get('/direct-purchase-orders/{directPurchaseOrder}/edit', [DirectPurchaseOrderController::class, 'edit'])->name('direct-purchase-orders.edit');
    Route::put('/direct-purchase-orders/{directPurchaseOrder}', [DirectPurchaseOrderController::class, 'update'])->name('direct-purchase-orders.update');
    Route::get('/direct-purchase-orders/categories', [DirectPurchaseOrderController::class, 'getAvailableCategories'])->name('direct-purchase-orders.categories');
    Route::get('/direct-purchase-orders/{directPurchaseOrder}', [PurchaseOrderController::class, 'showDirect'])->name('direct-purchase-orders.show');
    Route::post('/direct-purchase-orders/{directPurchaseOrder}/submit', [DirectPurchaseOrderController::class, 'submit'])->name('direct-purchase-orders.submit');
    Route::post('/direct-purchase-orders/{directPurchaseOrder}/approve', [DirectPurchaseOrderController::class, 'approve'])->name('direct-purchase-orders.approve');
    Route::post('/direct-purchase-orders/{directPurchaseOrder}/reject', [DirectPurchaseOrderController::class, 'reject'])->name('direct-purchase-orders.reject');
    Route::post('/direct-purchase-orders/{directPurchaseOrder}/return', [DirectPurchaseOrderController::class, 'return'])->name('direct-purchase-orders.return');

    // Purchase Orders
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/datatable/regular', [PurchaseOrderController::class, 'datatableRegular'])->name('purchase-orders.datatable.regular');
    Route::get('/purchase-orders/datatable/direct', [PurchaseOrderController::class, 'datatableDirect'])->name('purchase-orders.datatable.direct');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
});

// ============================================================================
//  Rutas comentadas (sin uso actual, conservadas por decisión)
// ============================================================================

// RFQ Planning (AJAX desde wizard - sin uso en vistas/controladores)
// Route::prefix('rfq/{requisition}/planning')->name('rfq.planning.')->group(function () {
//     Route::post('/save-strategy', [QuotationPlannerController::class, 'saveStrategy'])->name('save');
//     Route::post('/groups/create', [QuotationPlannerController::class, 'createGroup'])->name('groups.create');
//     Route::delete('/groups/{group}', [QuotationPlannerController::class, 'deleteGroup'])->name('groups.delete');
//     Route::post('/groups/{group}/add-items', [QuotationPlannerController::class, 'addItemsToGroup'])->name('groups.add-items');
//     Route::post('/groups/{group}/remove-items', [QuotationPlannerController::class, 'removeItemsFromGroup'])->name('groups.remove-items');
// });

// ============================================================================
//  Autenticación
// ============================================================================
require __DIR__ . '/auth.php';