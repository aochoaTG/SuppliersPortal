<?php

namespace App\Services;

use App\Models\AnnualBudget;
use App\Models\BudgetMovement;
use App\Models\Requisition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /** Obtiene (o crea) el presupuesto del centro/año. */
    public function getOrCreate(int $costCenterId, int $year, string $currency = 'MXN'): AnnualBudget
    {
        return AnnualBudget::firstOrCreate(
            ['fiscal_year' => $year, 'cost_center_id' => $costCenterId],
            ['amount_assigned' => 0, 'currency_code' => $currency]
        );
    }

    /** Snapshot resumido. */
    public function snapshot(int $costCenterId, int $year): array
    {
        $b = AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $year)
            ->first();

        if (!$b) {
            return [
                'assigned' => 0,
                'committed' => 0,
                'consumed' => 0,
                'released' => 0,
                'adjusted' => 0,
                'available' => 0,
                'currency' => 'MXN',
            ];
        }

        return [
            'assigned' => (float) $b->amount_assigned,
            'committed' => (float) $b->amount_committed,
            'consumed' => (float) $b->amount_consumed,
            'released' => (float) $b->amount_released,
            'adjusted' => (float) $b->amount_adjusted,
            'available' => $b->available(),
            'currency' => $b->currency_code,
        ];
    }

    /** ¿Se puede comprometer este monto? */
    public function canCommit(int $costCenterId, int $fiscalYear, float $amount): bool
    {
        $budget = $this->resolveApplicableBudget($costCenterId, $fiscalYear);
        if (!$budget) {
            return false; // no hay presupuesto definido para ese centro/año
        }

        $totals = $this->totals($budget->id); // ['committed','consumed','available']
        return ($totals['available'] ?? 0.0) + 1e-6 >= $amount;
    }

    /** Registra COMMIT al aprobar una requisición. */
    public function commit(Requisition $req, ?int $userId = null): void
    {
        DB::transaction(function () use ($req, $userId) {
            $budget = $this->getOrCreate($req->cost_center_id, $req->fiscal_year, $req->currency_code);

            // Aumenta comprometido
            $budget->amount_committed = (float) $budget->amount_committed + (float) $req->amount_requested;
            $budget->save();

            BudgetMovement::create([
                'fiscal_year' => $req->fiscal_year,
                'cost_center_id' => $req->cost_center_id,
                'annual_budget_id' => $budget->id,
                'requisition_id' => $req->id,
                'movement_type' => 'COMMIT',
                'amount' => $req->amount_requested,
                'currency_code' => $req->currency_code,
                'created_by' => $userId ?? Auth::id(),
                'note' => 'Compromiso por aprobación de requisición ' . $req->folio,
            ]);
        });
    }

    /** Libera compromiso (RELEASE) al cancelar/rechazar una req aprobada. */
    public function release(Requisition $req, ?int $userId = null, string $reason = 'Cancelación/Rechazo'): void
    {
        DB::transaction(function () use ($req, $userId, $reason) {
            $budget = $this->getOrCreate($req->cost_center_id, $req->fiscal_year, $req->currency_code);

            // Baja comprometido y sube liberado
            $budget->amount_committed = max(0, (float) $budget->amount_committed - (float) $req->amount_requested);
            $budget->amount_released = (float) $budget->amount_released + (float) $req->amount_requested;
            $budget->save();

            BudgetMovement::create([
                'fiscal_year' => $req->fiscal_year,
                'cost_center_id' => $req->cost_center_id,
                'annual_budget_id' => $budget->id,
                'requisition_id' => $req->id,
                'movement_type' => 'RELEASE',
                'amount' => $req->amount_requested,
                'currency_code' => $req->currency_code,
                'created_by' => $userId ?? Auth::id(),
                'note' => $reason,
            ]);
        });
    }

    /** Consume presupuesto (parcial o total) por OC/Factura/Recepción. */
    public function consume(Requisition $req, float $amount, ?int $userId = null, string $source = 'PO'): void
    {
        if ($amount <= 0)
            return;

        DB::transaction(function () use ($req, $amount, $userId, $source) {
            $budget = $this->getOrCreate($req->cost_center_id, $req->fiscal_year, $req->currency_code);

            // Baja comprometido (si queda) y sube consumido
            $budget->amount_committed = max(0, (float) $budget->amount_committed - $amount);
            $budget->amount_consumed = (string) (floatval($budget->amount_consumed) + $amount);
            $budget->save();

            BudgetMovement::create([
                'fiscal_year' => $req->fiscal_year,
                'cost_center_id' => $req->cost_center_id,
                'annual_budget_id' => $budget->id,
                'requisition_id' => $req->id,
                'movement_type' => 'CONSUME',
                'amount' => $amount,
                'currency_code' => $req->currency_code,
                'created_by' => $userId ?? Auth::id(),
                'source' => $source,
                'note' => 'Consumo ligado a requisición ' . $req->folio,
            ]);
        });
    }

    /**
     * Calcula totales del presupuesto:
     * - committed = COMPROMISO - LIBERACION - CONSUMO
     * - consumed  = CONSUMO
     * - adjusted  = AJUSTE_SUBIDA - AJUSTE_BAJADA
     * - available = amount_assigned + adjusted - committed - consumed
     */
    public function totals(int $annualBudgetId): array
    {
        $movements = BudgetMovement::select('movement_type', \DB::raw('SUM(amount) as total'))
            ->where('annual_budget_id', $annualBudgetId)
            ->groupBy('movement_type')
            ->pluck('total', 'movement_type');

        $sum = fn(string $t) => (float) ($movements[$t] ?? 0.0);

        $committed = $sum('COMPROMISO') - $sum('LIBERACION') - $sum('CONSUMO');
        $consumed = $sum('CONSUMO');
        $adjusted = $sum('AJUSTE_SUBIDA') - $sum('AJUSTE_BAJADA');

        $budget = AnnualBudget::findOrFail($annualBudgetId);
        $base = (float) $budget->amount_assigned;

        $available = $base + $adjusted - $committed - $consumed;

        return compact('committed', 'consumed', 'available');
    }

    /**
     * Devuelve el presupuesto aplicable a un centro de costo y año fiscal.
     * No crea registros. Si necesitas crear en automático, usa getOrCreate().
     */
    public function resolveApplicableBudget(int $costCenterId, int $fiscalYear): ?AnnualBudget
    {
        return AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $fiscalYear)
            ->first();
    }
}
