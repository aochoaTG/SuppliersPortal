<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string|null $cover_path
 * @property \Illuminate\Support\Carbon $published_at
 * @property \Illuminate\Support\Carbon|null $visible_until
 * @property bool $is_active
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string|null $cover_url
 * @property-read int|null $days_left
 * @property-read bool $is_currently_visible
 * @property-read bool $is_published
 * @property-read string $priority_class
 * @property-read string $priority_label
 * @property-read bool $should_display
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier> $suppliers
 * @property-read int|null $suppliers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnnouncementSupplier> $views
 * @property-read int|null $views_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement forSupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement highPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement priority(int $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement readyToShow()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereCoverPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereVisibleUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement withoutTrashed()
 */
	class Announcement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $announcement_id
 * @property int $supplier_id
 * @property \Illuminate\Support\Carbon|null $first_viewed_at
 * @property \Illuminate\Support\Carbon|null $last_viewed_at
 * @property bool $is_dismissed
 * @property \Illuminate\Support\Carbon|null $dismissed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Announcement $announcement
 * @property-read bool $has_been_dismissed
 * @property-read bool $has_been_viewed
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier dismissed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier viewed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereAnnouncementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereDismissedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereFirstViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereIsDismissed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereLastViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnouncementSupplier whereUpdatedAt($value)
 */
	class AnnouncementSupplier extends \Eloquent {}
}

namespace App\Models{
/**
 * AnnualBudget
 * 
 * Presupuesto anual para un Centro de Costo.
 * Solo para centros con budget_type = ANNUAL
 *
 * @property int $id
 * @property int $cost_center_id
 * @property int $fiscal_year
 * @property numeric $total_annual_amount
 * @property string $status
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\CostCenter $costCenter
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read mixed $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMonthlyDistribution> $monthlyDistributions
 * @property-read int|null $monthly_distributions_count
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget forYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget notDeleted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget planning()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereFiscalYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereTotalAnnualAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget withDetails()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AnnualBudget withoutTrashed()
 */
	class AnnualBudget extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $level_number
 * @property string $label
 * @property numeric $min_amount
 * @property numeric|null $max_amount
 * @property string $color_tag
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereColorTag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereLevelNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereMaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereMinAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApprovalLevel whereUpdatedAt($value)
 */
	class ApprovalLevel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $direct_purchase_order_id
 * @property int|null $purchase_order_id
 * @property int $cost_center_id
 * @property string $application_month
 * @property int $expense_category_id
 * @property numeric $committed_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon $committed_at
 * @property \Illuminate\Support\Carbon|null $released_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CostCenter $costCenter
 * @property-read \App\Models\DirectPurchaseOrder|null $directPurchaseOrder
 * @property-read \App\Models\ExpenseCategory $expenseCategory
 * @property-read \App\Models\PurchaseOrder|null $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment committed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment forBudget($costCenterId, $month, $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment fromDirectOrders()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment fromPurchaseOrders()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereApplicationMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereCommittedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereCommittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereDirectPurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereReleasedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetCommitment whereUpdatedAt($value)
 */
	class BudgetCommitment extends \Eloquent {}
}

namespace App\Models{
/**
 * BudgetMonthlyDistribution
 * 
 * Distribución mensual por categoría de gasto.
 * Desglosa el presupuesto anual en meses y categorías.
 * 
 * Estados del presupuesto:
 * - NORMAL: >70% disponible
 * - ALERTA: 30-70% disponible
 * - CRÍTICO: <30% disponible
 * - AGOTADO: 0% disponible
 *
 * @property int $id
 * @property int $annual_budget_id
 * @property int $expense_category_id
 * @property int $month
 * @property numeric $assigned_amount
 * @property numeric $consumed_amount
 * @property numeric $committed_amount
 * @property int $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AnnualBudget $annualBudget
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read \App\Models\ExpenseCategory $expenseCategory
 * @property-read string $color
 * @property-read string $month_label
 * @property-read string $status
 * @property-read string $status_label
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution exhausted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution forBudget($budgetId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution forCategory($categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution forMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution notDeleted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereAnnualBudgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereAssignedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereCommittedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereConsumedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution withAvailable()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMonthlyDistribution withoutTrashed()
 */
	class BudgetMonthlyDistribution extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $movement_type
 * @property int $fiscal_year
 * @property \Illuminate\Support\Carbon $movement_date
 * @property numeric $total_amount
 * @property string $justification
 * @property string $status
 * @property int $created_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMovementDetail> $adjustmentDetails
 * @property-read int|null $adjustment_details_count
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMovementDetail> $destinationDetails
 * @property-read int|null $destination_details_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMovementDetail> $details
 * @property-read int|null $details_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMovementDetail> $originDetails
 * @property-read int|null $origin_details_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement forYear($year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereFiscalYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereJustification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereMovementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereMovementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovement whereUpdatedAt($value)
 */
	class BudgetMovement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $budget_movement_id
 * @property string $detail_type
 * @property int $cost_center_id
 * @property int $month
 * @property int $expense_category_id
 * @property numeric $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BudgetMovement $budgetMovement
 * @property-read \App\Models\CostCenter $costCenter
 * @property-read \App\Models\ExpenseCategory $expenseCategory
 * @property-read string $month_name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail adjustment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail destination()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail forCategory($categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail forCostCenter($costCenterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail forMonth($month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail origin()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereBudgetMovementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereDetailType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BudgetMovementDetail whereUpdatedAt($value)
 */
	class BudgetMovementDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $source_system
 * @property string $source_company
 * @property string $source_external_id
 * @property string|null $name
 * @property string $rfc
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $state
 * @property string|null $email
 * @property string|null $website
 * @property string|null $bank
 * @property string|null $account_number
 * @property string|null $clabe
 * @property string|null $payment_method
 * @property string|null $currency
 * @property string|null $category
 * @property string|null $notes
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $display_name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereClabe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereRfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereSourceCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereSourceExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereSourceSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CatSupplier withoutTrashed()
 */
	class CatSupplier extends \Eloquent {}
}

namespace App\Models{
/**
 * Category
 * 
 * Catálogo de categorías de centros de costo.
 * Ejemplos: ADMINISTRACION, ENPROYECTO, STAFF, CORPORATIVO, OPERACIONES, ESTACIONES.
 * 
 * Notas de diseño:
 * - Ligero: solo los campos mínimos para operar.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $legal_name
 * @property string|null $rfc
 * @property string $locale
 * @property string $timezone
 * @property string $currency_code
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $domain
 * @property string|null $website
 * @property string|null $logo_path
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostCenter> $costCenters
 * @property-read int|null $cost_centers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Station> $stations
 * @property-read int|null $stations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company visibleTo(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLegalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereRfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereWebsite($value)
 */
	class Company extends \Eloquent {}
}

namespace App\Models{
/**
 * CostCenter
 * 
 * Centro de costo (estación, área, proyecto, etc.).
 * Puede ser ANNUAL (con presupuestos anuales) o FREE_CONSUMPTION (monto global).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $company_id
 * @property int $category_id
 * @property int $responsible_user_id
 * @property string $budget_type
 * @property numeric|null $global_amount
 * @property string|null $free_consumption_justification
 * @property int|null $authorized_by
 * @property \Illuminate\Support\Carbon|null $authorized_at
 * @property \Illuminate\Support\Carbon|null $validity_date
 * @property string $status
 * @property int $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnnualBudget> $annualBudgets
 * @property-read int|null $annual_budgets_count
 * @property-read \App\Models\User|null $authorizedBy
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read string $budget_type_label
 * @property-read string $status_label
 * @property-read \App\Models\User $responsible
 * @property-read \App\Models\User|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter annual()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter byCategory($categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter byCompany($companyId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter byResponsible($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter freeConsumption()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter hasAnnualBudget()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter notDeleted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereAuthorizedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereAuthorizedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereBudgetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereFreeConsumptionJustification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereGlobalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereResponsibleUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter whereValidityDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCenter withoutTrashed()
 */
	class CostCenter extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $abbreviated
 * @property bool $is_active
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereAbbreviated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $folio
 * @property int $supplier_id
 * @property int $cost_center_id
 * @property int $expense_category_id
 * @property string $application_month
 * @property string $justification
 * @property numeric $subtotal
 * @property numeric $iva_amount
 * @property numeric $total
 * @property string $currency
 * @property string|null $payment_terms
 * @property int|null $estimated_delivery_days
 * @property int|null $required_approval_level
 * @property int|null $assigned_approver_id
 * @property string $status
 * @property string|null $pdf_path
 * @property string|null $reception_notes
 * @property int $created_by
 * @property int|null $approved_by
 * @property int|null $rejected_by
 * @property int|null $returned_by
 * @property int|null $received_by
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property \Illuminate\Support\Carbon|null $returned_at
 * @property \Illuminate\Support\Carbon|null $issued_at
 * @property \Illuminate\Support\Carbon|null $received_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DirectPurchaseOrderApproval> $approvals
 * @property-read int|null $approvals_count
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\User|null $assignedApprover
 * @property-read \App\Models\BudgetCommitment|null $budgetCommitment
 * @property-read \App\Models\CostCenter $costCenter
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DirectPurchaseOrderDocument> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\ExpenseCategory $expenseCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DirectPurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $receiver
 * @property-read \App\Models\User|null $rejector
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder assignedToApprover($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder byCostCenter($costCenterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder byMonth(string $month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder createdBy($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereApplicationMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereAssignedApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereEstimatedDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereIssuedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereIvaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereJustification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder wherePdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereReceivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereReceptionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereRejectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereRequiredApprovalLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereReturnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereReturnedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder withStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrder withoutTrashed()
 */
	class DirectPurchaseOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $direct_purchase_order_id
 * @property int $approval_level
 * @property int $approver_user_id
 * @property string $action
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $approver
 * @property-read \App\Models\DirectPurchaseOrder $directPurchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval byApprover($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereApprovalLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereApproverUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereDirectPurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderApproval withAction(string $action)
 */
	class DirectPurchaseOrderApproval extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $direct_purchase_order_id
 * @property string $document_type
 * @property string $file_path
 * @property string $original_filename
 * @property int $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DirectPurchaseOrder $directPurchaseOrder
 * @property-read \App\Models\User $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument quotations()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereDirectPurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderDocument whereUploadedBy($value)
 */
	class DirectPurchaseOrderDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $direct_purchase_order_id
 * @property string $description
 * @property numeric $quantity
 * @property numeric $unit_price
 * @property numeric $iva_rate
 * @property numeric $subtotal
 * @property numeric $iva_amount
 * @property numeric $total
 * @property string|null $unit_of_measure
 * @property string|null $sku
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DirectPurchaseOrder $directPurchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem exempt()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereDirectPurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereIvaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereIvaRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereUnitOfMeasure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem withBorderIva()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DirectPurchaseOrderItem withGeneralIva()
 */
	class DirectPurchaseOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $company
 * @property string|null $employee_number
 * @property string|null $full_name
 * @property string|null $department
 * @property string|null $job_title
 * @property \Illuminate\Support\Carbon|null $hire_date
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $termination_date
 * @property bool|null $rehire_eligible
 * @property string|null $termination_reason
 * @property string|null $team
 * @property string|null $seniority
 * @property string|null $rfc
 * @property string|null $imss
 * @property string|null $curp
 * @property string|null $gender
 * @property numeric|null $vacation_balance
 * @property string|null $phone
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCurp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmployeeNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereImss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRehireEligible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSeniority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTerminationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTerminationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereVacationBalance($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * ExpenseCategory
 * 
 * Categorías estándar de gasto:
 * - MAT: Materiales (Insumos y materias primas)
 * - SER: Servicios (Servicios profesionales y técnicos)
 * - VIA: Viáticos (Gastos de viaje y representación)
 * - MAN: Mantenimiento (Mantenimiento de equipos e instalaciones)
 * - CAP: Capacitación (Programas de desarrollo de personal)
 * - TEC: Tecnología (Software, hardware y servicios TI)
 * - OTR: Otros Gastos (Gastos diversos no clasificados)
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property int $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMonthlyDistribution> $budgetMonthlyDistributions
 * @property-read int|null $budget_monthly_distributions_count
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $deletedBy
 * @property-read string $status_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetMonthlyDistribution> $monthlyDistributions
 * @property-read int|null $monthly_distributions_count
 * @property-read \App\Models\User|null $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory byCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory notDeleted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory withDistributions()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory withoutTrashed()
 */
	class ExpenseCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $reporter_name
 * @property string $reporter_email
 * @property string $module
 * @property string $severity
 * @property string $title
 * @property string $steps
 * @property string $expected
 * @property string $actual
 * @property string $reproducibility
 * @property string $impact
 * @property \Illuminate\Support\Carbon|null $happened_at
 * @property string|null $current_url
 * @property string|null $user_agent
 * @property string $status
 * @property string|null $image_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IncidentAttachment> $attachments
 * @property-read int|null $attachments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereActual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereCurrentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereExpected($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereHappenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereImpact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereReporterEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereReporterName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereReproducibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereSeverity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereSteps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Incident whereUserId($value)
 */
	class Incident extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncidentAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncidentAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncidentAttachment query()
 */
	class IncidentAttachment extends \Eloquent {}
}

namespace App\Models{
/**
 * Catálogo de Productos y Servicios
 * según ESPECIFICACIONES_TECNICAS_SISTEMA_CONTROL_PRESUPUESTAL.md
 * Sección 5.1: Entidad PRODUCTO/SERVICIO
 *
 * @property int $id
 * @property string $code
 * @property string $technical_description
 * @property string|null $short_name
 * @property string $product_type
 * @property int $category_id
 * @property string|null $subcategory
 * @property int $cost_center_id
 * @property int $company_id
 * @property string|null $brand
 * @property string|null $model
 * @property string $unit_of_measure
 * @property array<array-key, mixed>|null $specifications
 * @property numeric $estimated_price
 * @property string $currency_code
 * @property int|null $default_vendor_id
 * @property numeric|null $minimum_quantity
 * @property numeric|null $maximum_quantity
 * @property int|null $lead_time_days
 * @property string|null $account_major
 * @property string|null $account_sub
 * @property string|null $account_subsub
 * @property string $status
 * @property bool $is_active
 * @property string|null $rejection_reason
 * @property string|null $observations
 * @property string|null $internal_notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\CostCenter $costCenter
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Supplier|null $defaultVendor
 * @property-read \App\Models\User|null $deleter
 * @property-read \App\Models\ExpenseCategory|null $expenseCategory
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService byBrand(string $brand)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService byCompany(int $companyId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService byCostCenter(int $costCenterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService forRequisitions()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService pendingApproval()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService products()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService services()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereAccountMajor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereAccountSub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereAccountSubsub($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereDefaultVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereEstimatedPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereLeadTimeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereMaximumQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereMinimumQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereSubcategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereTechnicalDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereUnitOfMeasure($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductService withoutTrashed()
 */
	class ProductService extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $folio
 * @property int $requisition_id
 * @property int $supplier_id
 * @property int $quotation_summary_id
 * @property numeric $subtotal
 * @property numeric $iva_amount
 * @property numeric $total
 * @property string $currency
 * @property string|null $payment_terms
 * @property int|null $estimated_delivery_days
 * @property string $status
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Requisition $requisition
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereEstimatedDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereIvaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereQuotationSummaryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder withoutTrashed()
 */
	class PurchaseOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $requisition_item_id
 * @property string $description
 * @property numeric $quantity
 * @property numeric $unit_price
 * @property numeric $subtotal
 * @property numeric $iva_amount
 * @property numeric $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PurchaseOrder $purchaseOrder
 * @property-read \App\Models\RequisitionItem $requisitionItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereIvaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereRequisitionItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUpdatedAt($value)
 */
	class PurchaseOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $requisition_id
 * @property string $name
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read string $subtotal_formatted
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequisitionItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Requisition $requisition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rfq> $rfqs
 * @property-read int|null $rfqs_count
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup byRequisition(int $requisitionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup empty()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup withItems()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroup withoutTrashed()
 */
	class QuotationGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $quotation_group_id
 * @property int $requisition_item_id
 * @property string|null $notes
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QuotationGroup $quotationGroup
 * @property-read \App\Models\RequisitionItem $requisitionItem
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem whereQuotationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem whereRequisitionItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationGroupItem whereUpdatedAt($value)
 */
	class QuotationGroupItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $requisition_id
 * @property numeric $subtotal
 * @property numeric $iva_amount
 * @property numeric $total
 * @property int|null $approval_level_id
 * @property int|null $selected_supplier_id
 * @property string $approval_status
 * @property string|null $justification
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $rejected_by
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property string|null $rejection_reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\ApprovalLevel|null $approvalLevel
 * @property-read \App\Models\User|null $approver
 * @property-read string $approval_level_label
 * @property-read string $approval_status_label
 * @property-read string $iva_amount_formatted
 * @property-read string $subtotal_formatted
 * @property-read string $total_formatted
 * @property-read \App\Models\User|null $rejector
 * @property-read \App\Models\Requisition $requisition
 * @property-read \App\Models\Supplier|null $selectedSupplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary byApprovalLevel(string $level)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereApprovalLevelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereIvaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereJustification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereRejectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereSelectedSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QuotationSummary withoutTrashed()
 */
	class QuotationSummary extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property int $cost_center_id
 * @property int|null $department_id
 * @property string $folio
 * @property int|null $requested_by
 * @property \Illuminate\Support\Carbon|null $required_date
 * @property string|null $description
 * @property \App\Enum\RequisitionStatus|string $status
 * @property string|null $pause_reason
 * @property int|null $paused_by
 * @property \Illuminate\Support\Carbon|null $paused_at
 * @property int|null $reactivated_by
 * @property \Illuminate\Support\Carbon|null $reactivated_at
 * @property string|null $cancellation_reason
 * @property int|null $cancelled_by
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $rejection_reason
 * @property int|null $rejected_by
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property bool $validation_specs_clear
 * @property bool $validation_time_feasible
 * @property bool $validation_alternatives_evaluated
 * @property string|null $validated_at
 * @property int|null $validated_by
 * @property string|null $purchasing_validation_notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $canceller
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\CostCenter $costCenter
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RequisitionItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $pauser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationGroup> $quotationGroups
 * @property-read int|null $quotation_groups_count
 * @property-read \App\Models\QuotationSummary|null $quotationSummary
 * @property-read \App\Models\User|null $reactivator
 * @property-read \App\Models\User|null $rejector
 * @property-read \App\Models\User|null $requester
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rfq> $rfqs
 * @property-read int|null $rfqs_count
 * @property-read \App\Models\User|null $updater
 * @property-read \App\Models\User|null $validator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition byCostCenter(int $costCenterId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition byYear(int $year)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition forSupplierPortal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition inQuotation()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition myRequisitions(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition ordersIssued()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition paused()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition pendingBudgetAdjustment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition quoted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition submitted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCancelledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCostCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition wherePauseReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition wherePausedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition wherePausedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition wherePurchasingValidationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereReactivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereReactivatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereRejectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereRequiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereValidatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereValidationAlternativesEvaluated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereValidationSpecsClear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition whereValidationTimeFeasible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Requisition withoutTrashed()
 */
	class Requisition extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $requisition_id
 * @property int $product_service_id
 * @property int $line_number
 * @property string $item_category
 * @property string $product_code
 * @property string $description
 * @property int $expense_category_id
 * @property numeric $quantity
 * @property string $unit
 * @property int|null $suggested_vendor_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpenseCategory $expenseCategory
 * @property-read \App\Models\ProductService $product
 * @property-read \App\Models\ProductService $productService
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuotationGroup> $quotationGroups
 * @property-read int|null $quotation_groups_count
 * @property-read \App\Models\Requisition $requisition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RfqResponse> $rfqResponses
 * @property-read int|null $rfq_responses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rfq> $rfqs
 * @property-read int|null $rfqs_count
 * @property-read \App\Models\Supplier|null $suggestedVendor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem ofExpenseCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem ofRequisition(int $requisitionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem orderedByLine()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereItemCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereLineNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereProductCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereProductServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereSuggestedVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisitionItem withRelations()
 */
	class RequisitionItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $folio
 * @property int $requisition_id
 * @property int|null $quotation_group_id
 * @property int|null $requisition_item_id
 * @property int|null $supplier_id
 * @property string $source
 * @property string|null $external_contact_method
 * @property string|null $external_notes
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $response_deadline
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int|null $cancelled_by
 * @property string|null $cancellation_reason
 * @property string|null $message
 * @property string|null $notes
 * @property string|null $requirements
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $canceller
 * @property-read \App\Models\User|null $creator
 * @property-read int|null $days_remaining
 * @property-read string $type_label
 * @property-read \App\Models\QuotationGroup|null $quotationGroup
 * @property-read \App\Models\QuotationSummary|null $quotationSummary
 * @property-read \App\Models\Requisition $requisition
 * @property-read \App\Models\RequisitionItem|null $requisitionItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RfqResponse> $rfqResponses
 * @property-read int|null $rfq_responses_count
 * @property-read \App\Models\Supplier|null $supplier
 * @property-read \App\Models\RfqSupplier|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supplier> $suppliers
 * @property-read int|null $suppliers_count
 * @property-read \App\Models\User|null $updater
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq byRequisition(int $requisitionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq bySupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq external()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq portal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq responded()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq sent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCancelledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereExternalContactMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereExternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereFolio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereQuotationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereRequisitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereRequisitionItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereResponseDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rfq withoutTrashed()
 */
	class Rfq extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $rfq_id
 * @property int $supplier_id
 * @property int $requisition_item_id
 * @property \Illuminate\Support\Carbon|null $quotation_date
 * @property int|null $validity_days
 * @property string|null $supplier_quotation_number
 * @property numeric $unit_price
 * @property numeric $quantity
 * @property numeric $subtotal
 * @property numeric $iva_rate
 * @property numeric $iva_amount
 * @property numeric $total
 * @property numeric|null $discount_percentage
 * @property numeric|null $discount_amount
 * @property string $currency
 * @property int|null $delivery_days
 * @property string|null $payment_terms
 * @property string|null $warranty_terms
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $specifications
 * @property string|null $notes
 * @property string|null $attachment_path
 * @property bool $meets_specs
 * @property int|null $score
 * @property string|null $evaluation_notes
 * @property string $status
 * @property string|null $selection_justification
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property int|null $evaluated_by
 * @property \Illuminate\Support\Carbon|null $evaluated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $evaluator
 * @property-read int|null $days_remaining
 * @property-read string $discount_amount_formatted
 * @property-read \Carbon\Carbon|null $expiry_date
 * @property-read string $iva_amount_formatted
 * @property-read string $subtotal_formatted
 * @property-read string $total_formatted
 * @property-read string $unit_price_formatted
 * @property-read \App\Models\RequisitionItem $requisitionItem
 * @property-read \App\Models\Rfq $rfq
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RfqResponse> $rfqResponses
 * @property-read int|null $rfq_responses_count
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse byItem(int $itemId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse byRfq(int $rfqId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse bySupplier(int $supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse draft()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse meetsSpecs()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse submitted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse valid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereAttachmentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereDeliveryDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereDiscountPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereEvaluatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereEvaluatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereEvaluationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereIvaAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereIvaRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereMeetsSpecs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereQuotationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereRequisitionItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereRfqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereSelectionJustification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereSupplierQuotationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereValidityDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse whereWarrantyTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqResponse withoutTrashed()
 */
	class RfqResponse extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $rfq_id
 * @property int $supplier_id
 * @property \Illuminate\Support\Carbon|null $invited_at
 * @property \Illuminate\Support\Carbon|null $responded_at
 * @property string|null $quotation_pdf_path
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $quotation_pdf_url
 * @property-read \App\Models\Rfq $rfq
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereInvitedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereQuotationPdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereRespondedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereRfqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RfqSupplier whereUpdatedAt($value)
 */
	class RfqSupplier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $number
 * @property string|null $rfc
 * @property string|null $company_name
 * @property string|null $situation
 * @property string|null $sat_presumption_notice_date
 * @property \Illuminate\Support\Carbon|null $sat_presumed_publication_date
 * @property string|null $dof_presumption_notice_date
 * @property \Illuminate\Support\Carbon|null $dof_presumed_pub_date
 * @property \Illuminate\Support\Carbon|null $sat_definitive_publication_date
 * @property \Illuminate\Support\Carbon|null $dof_definitive_publication_date
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereDofDefinitivePublicationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereDofPresumedPubDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereDofPresumptionNoticeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereRfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereSatDefinitivePublicationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereSatPresumedPublicationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereSatPresumptionNoticeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereSituation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SatEfos69b whereUpdatedAt($value)
 */
	class SatEfos69b extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $company_id
 * @property string $station_name
 * @property string|null $country
 * @property string|null $state
 * @property string|null $municipality
 * @property string|null $address
 * @property string|null $expedition_place
 * @property string|null $server_ip
 * @property string|null $database_name
 * @property string|null $cre_permit
 * @property string|null $email
 * @property string|null $source_system
 * @property string|null $external_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company $company
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereCrePermit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereDatabaseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereExpeditionPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereMunicipality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereServerIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereSourceSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereStationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereUpdatedAt($value)
 */
	class Station extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $company_name
 * @property string $rfc
 * @property string $address
 * @property string $phone_number
 * @property string $email
 * @property string $contact_person
 * @property string|null $contact_phone
 * @property string $supplier_type
 * @property string $tax_regime
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property string|null $clabe
 * @property string|null $currency
 * @property string|null $swift_bic
 * @property string|null $iban
 * @property string|null $bank_address
 * @property string|null $aba_routing
 * @property string|null $us_bank_name
 * @property string $status
 * @property bool $provides_specialized_services
 * @property string|null $repse_registration_number
 * @property \Illuminate\Support\Carbon|null $repse_expiry_date
 * @property array<array-key, mixed>|null $specialized_services_types
 * @property string|null $economic_activity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierDocument> $documents
 * @property-read int|null $documents_count
 * @property-read string|null $efos_status
 * @property-read bool $is_efos
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RfqResponse> $rfqResponses
 * @property-read int|null $rfq_responses_count
 * @property-read \App\Models\RfqSupplier|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Rfq> $rfqs
 * @property-read int|null $rfqs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupplierSiroc> $sirocs
 * @property-read int|null $sirocs_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier active()
 * @method static \Database\Factories\SupplierFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier notEfos69b()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier search(?string $term)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAbaRouting($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereBankAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereClabe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereEconomicActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereIban($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereProvidesSpecializedServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereRepseExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereRepseRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereRfc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereSpecializedServicesTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereSupplierType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereSwiftBic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereTaxRegime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUsBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUserId($value)
 */
	class Supplier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $supplier_id
 * @property int|null $uploaded_by
 * @property int|null $reviewed_by
 * @property string $doc_type
 * @property string $path_file
 * @property int|null $size_bytes
 * @property string|null $mime_type
 * @property string $status
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon $uploaded_at
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\Supplier $supplier
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereDocType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument wherePathFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereSizeBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierDocument whereUploadedBy($value)
 */
	class SupplierDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $supplier_id
 * @property string $siroc_number
 * @property string|null $contract_number
 * @property string|null $work_name
 * @property string|null $work_location
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $siroc_file
 * @property string $status
 * @property string|null $observations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereContractNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereSirocFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereSirocNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereWorkLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupplierSiroc whereWorkName($value)
 */
	class SupplierSiroc extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property numeric $rate_percent
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereRatePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereUpdatedAt($value)
 */
	class Tax extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $first_name
 * @property string|null $last_name
 * @property bool $is_active
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $job_title
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $companies
 * @property-read int|null $companies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CostCenter> $costCenters
 * @property-read int|null $cost_centers_count
 * @property-read \App\Models\Employee|null $employee
 * @property-read string $full_name
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Supplier|null $supplier
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

