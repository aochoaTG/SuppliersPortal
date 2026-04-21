<?php

namespace App\Services;

use App\Models\AnnualBudget;
use App\Models\BudgetCommitment;
use App\Models\BudgetMonthlyDistribution;
use App\Models\CostCenter;
use App\Models\DirectPurchaseOrder;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BudgetAllocationService
{
    public function checkAvailability(
        int $costCenterId,
        int $year,
        int $month,
        int $categoryId,
        float $requiredAmount
    ): array {
        $costCenter = CostCenter::find($costCenterId);

        if (! $costCenter) {
            return ['available' => false, 'message' => 'Centro de costo no encontrado.'];
        }

        if ($costCenter->isFreeConsumption()) {
            return ['available' => true, 'message' => 'Centro de costo de consumo libre.'];
        }

        $budget = AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $year)
            ->where('status', 'APROBADO')
            ->first();

        if (! $budget) {
            return ['available' => false, 'message' => 'No existe presupuesto aprobado para el centro de costo y año fiscal seleccionados.'];
        }

        $distribution = BudgetMonthlyDistribution::where('annual_budget_id', $budget->id)
            ->where('month', $month)
            ->where('expense_category_id', $categoryId)
            ->first();

        if (! $distribution) {
            return ['available' => false, 'message' => 'No existe distribución mensual para la categoría y mes seleccionados.'];
        }

        $available = $distribution->getAvailableAmount();

        return [
            'available' => $available + 0.000001 >= $requiredAmount,
            'message' => $available + 0.000001 >= $requiredAmount
                ? 'Presupuesto disponible.'
                : sprintf(
                    'Presupuesto insuficiente. Disponible: $%s, requerido: $%s.',
                    number_format($available, 2),
                    number_format($requiredAmount, 2)
                ),
            'available_amount' => $available,
            'assigned_amount' => (float) $distribution->assigned_amount,
            'consumed_amount' => (float) $distribution->consumed_amount,
            'committed_amount' => (float) $distribution->committed_amount,
        ];
    }

    public function commitOrder(Model $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->getOrderBudgetLines($order) as $line) {
                $this->commitLine($order, $line);
            }
        });
    }

    public function syncCommitmentTrace(Model $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->getOrderBudgetLines($order) as $line) {
                $commitment = $this->findCommitment($order, $line['expense_category_id']) ?? new BudgetCommitment();

                if ($commitment->exists && $commitment->status === 'RECEIVED') {
                    continue;
                }

                $this->fillCommitment($commitment, $order, $line, 'COMMITTED');
                $commitment->committed_at = $commitment->committed_at ?? now();
                $commitment->released_at = null;
                $commitment->received_at = null;
                $commitment->save();
            }
        });
    }

    public function releaseOrder(Model $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->getOrderBudgetLines($order) as $line) {
                $this->releaseLine($order, $line);
            }
        });
    }

    public function releaseTrace(Model $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->getOrderBudgetLines($order) as $line) {
                $commitment = $this->findCommitment($order, $line['expense_category_id']);

                if (! $commitment || $commitment->status !== 'COMMITTED') {
                    continue;
                }

                $commitment->update([
                    'status' => 'RELEASED',
                    'released_at' => now(),
                ]);
            }
        });
    }

    public function consumeOrder(Model $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($this->getOrderBudgetLines($order) as $line) {
                $this->consumeLine($order, $line);
            }
        });
    }

    private function commitLine(Model $order, array $line): void
    {
        $commitment = $this->findCommitment($order, $line['expense_category_id']);

        if ($commitment && $commitment->status !== 'RELEASED') {
            return;
        }

        if ($line['budget_type'] === 'ANNUAL') {
            $distribution = $this->resolveDistribution(
                $line['cost_center_id'],
                $line['year'],
                $line['month'],
                $line['expense_category_id']
            );

            if (! $distribution->commitAmount($line['amount'])) {
                throw new RuntimeException(
                    "No se pudo comprometer presupuesto para la categoría {$line['expense_category_id']}."
                );
            }
        }

        $commitment = $commitment ?? new BudgetCommitment();
        $this->fillCommitment($commitment, $order, $line, 'COMMITTED');
        $commitment->committed_at = now();
        $commitment->released_at = null;
        $commitment->received_at = null;
        $commitment->save();
    }

    private function releaseLine(Model $order, array $line): void
    {
        $commitment = $this->findCommitment($order, $line['expense_category_id']);

        if (! $commitment || $commitment->status !== 'COMMITTED') {
            return;
        }

        if ($line['budget_type'] === 'ANNUAL') {
            $distribution = $this->resolveDistribution(
                $line['cost_center_id'],
                $line['year'],
                $line['month'],
                $line['expense_category_id']
            );

            if (! $distribution->releaseCommitment((float) $commitment->committed_amount)) {
                throw new RuntimeException(
                    "No se pudo liberar presupuesto para la categoría {$line['expense_category_id']}."
                );
            }
        }

        $commitment->update([
            'status' => 'RELEASED',
            'released_at' => now(),
        ]);
    }

    private function consumeLine(Model $order, array $line): void
    {
        $commitment = $this->findCommitment($order, $line['expense_category_id']);

        if (! $commitment || $commitment->status === 'RECEIVED') {
            return;
        }

        if ($line['budget_type'] === 'ANNUAL') {
            $distribution = $this->resolveDistribution(
                $line['cost_center_id'],
                $line['year'],
                $line['month'],
                $line['expense_category_id']
            );

            if (! $distribution->commitToConsume((float) $commitment->committed_amount)) {
                throw new RuntimeException(
                    "No se pudo consumir presupuesto para la categoría {$line['expense_category_id']}."
                );
            }
        }

        $commitment->update([
            'status' => 'RECEIVED',
            'received_at' => now(),
        ]);
    }

    private function resolveDistribution(
        int $costCenterId,
        int $year,
        int $month,
        int $categoryId
    ): BudgetMonthlyDistribution {
        $budget = AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $year)
            ->where('status', 'APROBADO')
            ->first();

        if (! $budget) {
            throw new RuntimeException("No existe presupuesto aprobado para el centro de costo {$costCenterId} en {$year}.");
        }

        $distribution = BudgetMonthlyDistribution::where('annual_budget_id', $budget->id)
            ->where('month', $month)
            ->where('expense_category_id', $categoryId)
            ->first();

        if (! $distribution) {
            throw new RuntimeException("No existe distribución mensual para la categoría {$categoryId} en {$month}/{$year}.");
        }

        return $distribution;
    }

    private function getOrderBudgetLines(Model $order): array
    {
        if ($order instanceof DirectPurchaseOrder) {
            $order->loadMissing('items', 'costCenter');

            return $order->items
                ->groupBy('expense_category_id')
                ->map(function ($items, $categoryId) use ($order) {
                    return [
                        'cost_center_id' => (int) $order->cost_center_id,
                        'expense_category_id' => (int) $categoryId,
                        'amount' => (float) $items->sum('total'),
                        'year' => (int) substr((string) $order->application_month, 0, 4),
                        'month' => (int) substr((string) $order->application_month, 5, 2),
                        'application_month' => $order->application_month,
                        'budget_type' => $order->costCenter?->budget_type ?? 'ANNUAL',
                    ];
                })
                ->values()
                ->all();
        }

        if ($order instanceof PurchaseOrder) {
            $order->loadMissing('items.requisitionItem', 'requisition.costCenter');
            $applicationMonth = $order->created_at->format('Y-m');

            return $order->items
                ->groupBy(fn($item) => $item->requisitionItem?->expense_category_id)
                ->filter(fn($items, $categoryId) => ! empty($categoryId))
                ->map(function ($items, $categoryId) use ($order, $applicationMonth) {
                    return [
                        'cost_center_id' => (int) $order->requisition->cost_center_id,
                        'expense_category_id' => (int) $categoryId,
                        'amount' => (float) $items->sum('total'),
                        'year' => (int) substr($applicationMonth, 0, 4),
                        'month' => (int) substr($applicationMonth, 5, 2),
                        'application_month' => $applicationMonth,
                        'budget_type' => $order->requisition->costCenter?->budget_type ?? 'ANNUAL',
                    ];
                })
                ->values()
                ->all();
        }

        throw new RuntimeException('Tipo de orden no soportado para asignación presupuestal.');
    }

    private function findCommitment(Model $order, int $expenseCategoryId): ?BudgetCommitment
    {
        $query = BudgetCommitment::query()
            ->where('expense_category_id', $expenseCategoryId);

        if ($order instanceof DirectPurchaseOrder) {
            $query->where('direct_purchase_order_id', $order->id);
        } elseif ($order instanceof PurchaseOrder) {
            $query->where('purchase_order_id', $order->id);
        } else {
            throw new RuntimeException('Tipo de orden no soportado para compromisos.');
        }

        return $query->latest('id')->first();
    }

    private function fillCommitment(BudgetCommitment $commitment, Model $order, array $line, string $status): void
    {
        $commitment->direct_purchase_order_id = $order instanceof DirectPurchaseOrder ? $order->id : null;
        $commitment->purchase_order_id = $order instanceof PurchaseOrder ? $order->id : null;
        $commitment->cost_center_id = $line['cost_center_id'];
        $commitment->application_month = $line['application_month'];
        $commitment->expense_category_id = $line['expense_category_id'];
        $commitment->committed_amount = $line['amount'];
        $commitment->status = $status;
    }
}
