<?php

namespace App\Console\Commands;

use App\Models\DirectPurchaseOrder;
use App\Models\PurchaseOrder;
use App\Notifications\DirectPurchaseOrderClosedByInactivityNotification;
use App\Notifications\DirectPurchaseOrderInactivityWarningNotification;
use App\Notifications\PurchaseOrderClosedByInactivityNotification;
use App\Notifications\PurchaseOrderInactivityWarningNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseInactivePurchaseOrders extends Command
{
    protected $signature = 'purchase-orders:close-inactive
                            {--dry-run : Simula el proceso sin hacer cambios}';

    protected $description = 'Cierra automáticamente las OC (directas y estándar) que superaron el plazo de aprobación por inactividad y envía alertas preventivas a las próximas a vencer.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $now = now();

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios en la base de datos.');
        }

        $this->info('=== Cierre automático por inactividad (' . $now->format('d/m/Y H:i') . ') ===');

        // ── 1. OC DIRECTAS (límite: 7 días desde submitted_at) ──────────────
        $this->info('');
        $this->info('📋 Procesando Órdenes de Compra Directas (OCD)...');

        $this->processDirectPurchaseOrders($now, $dryRun);

        // ── 2. OC ESTÁNDAR (límite: 10 días desde created_at) ───────────────
        $this->info('');
        $this->info('📋 Procesando Órdenes de Compra Estándar...');

        $this->processStandardPurchaseOrders($now, $dryRun);

        $this->info('');
        $this->info('✅ Proceso completado.');

        return Command::SUCCESS;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OC DIRECTAS
    // ─────────────────────────────────────────────────────────────────────────

    private function processDirectPurchaseOrders($now, bool $dryRun): void
    {
        $inactivityDays = DirectPurchaseOrder::INACTIVITY_DAYS;      // 7
        $warningThreshold = $inactivityDays - 3;                      // 4

        // 1a. Cerrar OCD vencidas (7+ días desde submitted_at, aún PENDING_APPROVAL)
        $closedCount = 0;
        DirectPurchaseOrder::where('status', 'PENDING_APPROVAL')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<=', $now->copy()->subDays($inactivityDays))
            ->with(['assignedApprover', 'creator', 'items.expenseCategory', 'costCenter'])
            ->chunk(200, function ($ocds) use ($now, $dryRun, &$closedCount) {
                foreach ($ocds as $ocd) {
                    $this->closeDirectPurchaseOrder($ocd, $now, $dryRun);
                    $closedCount++;
                }
            });

        if ($closedCount === 0) {
            $this->line('  • No hay OCD vencidas para cerrar.');
        } else {
            $this->line("  • Cerrando {$closedCount} OCD vencida(s)...");
        }

        // 1b. Enviar alerta preventiva (4-7 días desde submitted_at, warning no enviado)
        $alertCount = 0;
        DirectPurchaseOrder::where('status', 'PENDING_APPROVAL')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<=', $now->copy()->subDays($warningThreshold))
            ->where('submitted_at', '>', $now->copy()->subDays($inactivityDays))
            ->whereNull('inactivity_warning_sent_at')
            ->with(['assignedApprover', 'creator'])
            ->chunk(200, function ($ocds) use ($dryRun, &$alertCount) {
                foreach ($ocds as $ocd) {
                    $this->sendDirectPurchaseOrderWarning($ocd, $dryRun);
                    $alertCount++;
                }
            });

        if ($alertCount === 0) {
            $this->line('  • No hay OCD en zona de alerta (3 días antes del cierre).');
        } else {
            $this->line("  • Enviando alerta preventiva a {$alertCount} OCD...");
        }
    }

    private function closeDirectPurchaseOrder(DirectPurchaseOrder $ocd, $now, bool $dryRun): void
    {
        $this->line("    → [OCD] Cerrando: {$ocd->folio} (submitted: {$ocd->submitted_at->format('d/m/Y')})");

        if ($dryRun) {
            return;
        }

        try {
            DB::beginTransaction();

            $ocd->update([
                'status' => 'CLOSED_BY_INACTIVITY',
                'closed_at' => $now,
            ]);

            // Registrar en auditoría (tabla de aprobaciones)
            $ocd->approvals()->create([
                'approval_level' => $ocd->required_approval_level ?? 1,
                'approver_user_id' => null, // cierre automático del sistema
                'action' => 'CLOSED_BY_INACTIVITY',
                'comments' => 'Cerrada automáticamente por inactividad. Sin aprobación en ' . DirectPurchaseOrder::INACTIVITY_DAYS . ' días naturales desde el envío (' . $ocd->submitted_at->format('d/m/Y') . ').',
                'approved_at' => $now,
            ]);

            // Nota: el presupuesto NO fue comprometido (se compromete al aprobar, no al enviar),
            // por lo que no es necesario liberarlo en este punto.

            DB::commit();

            // Notificar al aprobador asignado
            if ($ocd->assignedApprover) {
                $ocd->assignedApprover->notify(new DirectPurchaseOrderClosedByInactivityNotification($ocd));
            }

            // Notificar al solicitante
            if ($ocd->creator) {
                $ocd->creator->notify(new DirectPurchaseOrderClosedByInactivityNotification($ocd));
            }

            Log::info("[CloseInactive] OCD {$ocd->folio} (ID: {$ocd->id}) cerrada por inactividad.", [
                'submitted_at' => $ocd->submitted_at,
                'closed_at' => $now,
            ]);

            $this->info("      ✓ {$ocd->folio} cerrada. Aprobador y solicitante notificados.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[CloseInactive] Error al cerrar OCD {$ocd->folio}: " . $e->getMessage());
            $this->error("      ✗ Error al cerrar {$ocd->folio}: " . $e->getMessage());
        }
    }

    private function sendDirectPurchaseOrderWarning(DirectPurchaseOrder $ocd, bool $dryRun): void
    {
        $deadline = $ocd->getAutoCloseDeadline();
        $this->line("    → [OCD] Alerta: {$ocd->folio} — vence el {$deadline?->format('d/m/Y')}");

        if ($dryRun) {
            return;
        }

        try {
            $ocd->update(['inactivity_warning_sent_at' => now()]);

            if ($ocd->assignedApprover) {
                $ocd->assignedApprover->notify(new DirectPurchaseOrderInactivityWarningNotification($ocd));
            }

            if ($ocd->creator) {
                $ocd->creator->notify(new DirectPurchaseOrderInactivityWarningNotification($ocd));
            }

            Log::info("[CloseInactive] Alerta de inactividad enviada para OCD {$ocd->folio}.", [
                'submitted_at' => $ocd->submitted_at,
                'deadline' => $deadline,
            ]);

            $this->info("      ✓ Alerta enviada para {$ocd->folio}.");
        } catch (\Exception $e) {
            Log::error("[CloseInactive] Error al enviar alerta OCD {$ocd->folio}: " . $e->getMessage());
            $this->error("      ✗ Error al enviar alerta para {$ocd->folio}: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OC ESTÁNDAR
    // ─────────────────────────────────────────────────────────────────────────

    private function processStandardPurchaseOrders($now, bool $dryRun): void
    {
        $inactivityDays = PurchaseOrder::INACTIVITY_DAYS;   // 10
        $warningThreshold = $inactivityDays - 3;             // 7

        // 2a. Cerrar OC estándar vencidas (10+ días desde created_at, aún OPEN)
        $closedCount = 0;
        PurchaseOrder::where('status', 'OPEN')
            ->where('created_at', '<=', $now->copy()->subDays($inactivityDays))
            ->with(['creator', 'supplier', 'requisition'])
            ->chunk(200, function ($pos) use ($now, $dryRun, &$closedCount) {
                foreach ($pos as $po) {
                    $this->closeStandardPurchaseOrder($po, $now, $dryRun);
                    $closedCount++;
                }
            });

        if ($closedCount === 0) {
            $this->line('  • No hay OC estándar vencidas para cerrar.');
        } else {
            $this->line("  • Cerrando {$closedCount} OC estándar vencida(s)...");
        }

        // 2b. Enviar alerta preventiva (7-10 días desde created_at, warning no enviado)
        $alertCount = 0;
        PurchaseOrder::where('status', 'OPEN')
            ->where('created_at', '<=', $now->copy()->subDays($warningThreshold))
            ->where('created_at', '>', $now->copy()->subDays($inactivityDays))
            ->whereNull('inactivity_warning_sent_at')
            ->with(['creator', 'supplier'])
            ->chunk(200, function ($pos) use ($dryRun, &$alertCount) {
                foreach ($pos as $po) {
                    $this->sendStandardPurchaseOrderWarning($po, $dryRun);
                    $alertCount++;
                }
            });

        if ($alertCount === 0) {
            $this->line('  • No hay OC estándar en zona de alerta (3 días antes del cierre).');
        } else {
            $this->line("  • Enviando alerta preventiva a {$alertCount} OC estándar...");
        }
    }

    private function closeStandardPurchaseOrder(PurchaseOrder $po, $now, bool $dryRun): void
    {
        $this->line("    → [OC] Cerrando: {$po->folio} (generada: {$po->created_at->format('d/m/Y')})");

        if ($dryRun) {
            return;
        }

        try {
            DB::beginTransaction();

            $po->update([
                'status' => 'CLOSED_BY_INACTIVITY',
                'closed_at' => $now,
            ]);

            // Liberar presupuesto comprometido mediante los items y su distribución presupuestal
            app(\App\Services\BudgetAllocationService::class)->releaseOrder($po);

            DB::commit();

            // Notificar al creador (buyer responsable)
            if ($po->creator) {
                $po->creator->notify(new PurchaseOrderClosedByInactivityNotification($po));
            }

            // Notificar al solicitante de la requisición origen (si aplica)
            $requisitionCreator = $po->requisition?->creator ?? null;
            if ($requisitionCreator && $requisitionCreator->id !== $po->creator?->id) {
                $requisitionCreator->notify(new PurchaseOrderClosedByInactivityNotification($po));
            }

            Log::info("[CloseInactive] OC Estándar {$po->folio} (ID: {$po->id}) cerrada por inactividad.", [
                'created_at' => $po->created_at,
                'closed_at' => $now,
            ]);

            $this->info("      ✓ {$po->folio} cerrada. Responsables notificados.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[CloseInactive] Error al cerrar OC Estándar {$po->folio}: " . $e->getMessage());
            $this->error("      ✗ Error al cerrar {$po->folio}: " . $e->getMessage());
        }
    }

    /**
     * Libera el presupuesto comprometido al cerrar una OC estándar por inactividad.
     * Itera los items de la OC, localiza las distribuciones presupuestales y llama
     * a releaseCommitment() para devolver el monto a "Disponible".
     */
    private function releaseBudgetForStandardPO(PurchaseOrder $po): void
    {
        if (!$po->requisition) {
            Log::warning("[CloseInactive] OC Estándar {$po->folio}: sin requisición origen. Presupuesto no liberado automáticamente.");
            return;
        }

        $requisition = $po->requisition;
        $costCenterId = $requisition->cost_center_id ?? null;
        $applicationMonth = $po->created_at->format('Y-m');

        if (!$costCenterId) {
            Log::warning("[CloseInactive] OC Estándar {$po->folio}: no se pudo determinar el centro de costo.");
            return;
        }

        $date = \Carbon\Carbon::parse($applicationMonth . '-01');
        $year = $date->year;
        $month = $date->month;

        $budget = \App\Models\AnnualBudget::where('cost_center_id', $costCenterId)
            ->where('fiscal_year', $year)
            ->where('status', 'APROBADO')
            ->first();

        if (!$budget) {
            Log::info("[CloseInactive] OC Estándar {$po->folio}: sin presupuesto anual aprobado para CC {$costCenterId}, año {$year}. Nada que liberar.");
            return;
        }

        // Agrupar items por categoría y liberar por categoría
        $itemsByCategory = $po->items->groupBy('expense_category_id');

        foreach ($itemsByCategory as $categoryId => $items) {
            $amountToRelease = (float) $items->sum('total');

            $distribution = $budget->monthlyDistributions()
                ->where('month', $month)
                ->where('expense_category_id', $categoryId)
                ->first();

            if ($distribution) {
                if ($distribution->releaseCommitment($amountToRelease)) {
                    Log::info("[CloseInactive] {$po->folio}: liberados \${$amountToRelease} en categoría {$categoryId}, mes {$month}/{$year}.");
                } else {
                    Log::error("[CloseInactive] {$po->folio}: fallo al liberar \${$amountToRelease} en categoría {$categoryId}.");
                }
            }
        }
    }

    private function sendStandardPurchaseOrderWarning(PurchaseOrder $po, bool $dryRun): void
    {
        $deadline = $po->getAutoCloseDeadline();
        $this->line("    → [OC] Alerta: {$po->folio} — vence el {$deadline->format('d/m/Y')}");

        if ($dryRun) {
            return;
        }

        try {
            $po->update(['inactivity_warning_sent_at' => now()]);

            if ($po->creator) {
                $po->creator->notify(new PurchaseOrderInactivityWarningNotification($po));
            }

            $requisitionCreator = $po->requisition?->creator ?? null;
            if ($requisitionCreator && $requisitionCreator->id !== $po->creator?->id) {
                $requisitionCreator->notify(new PurchaseOrderInactivityWarningNotification($po));
            }

            Log::info("[CloseInactive] Alerta de inactividad enviada para OC Estándar {$po->folio}.", [
                'created_at' => $po->created_at,
                'deadline' => $deadline,
            ]);

            $this->info("      ✓ Alerta enviada para {$po->folio}.");
        } catch (\Exception $e) {
            Log::error("[CloseInactive] Error al enviar alerta OC {$po->folio}: " . $e->getMessage());
            $this->error("      ✗ Error al enviar alerta para {$po->folio}: " . $e->getMessage());
        }
    }
}
