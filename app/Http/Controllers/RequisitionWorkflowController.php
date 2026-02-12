<?php

namespace App\Http\Controllers;

use App\Enum\RequisitionStatus;
use App\Models\Requisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\RequisitionSubmittedNotification;
use App\Notifications\RequisitionRejectedNotification;
use App\Notifications\RequisitionInQuotationNotification;
use Illuminate\Support\Facades\Log;

class RequisitionWorkflowController extends Controller
{
    /**
     * Bandeja: requisiciones enviadas a Compras.
     * Según tu migración, no hay "aprobación" - van directamente a Compras
     */
    public function validationInbox(Request $request)
    {
        // En tu migración, una vez enviada debería tener estado 'submitted'
        // Pero según tu Enum parece que es 'pending'
        $rows = Requisition::with([
            'company:id,name',
            'costCenter:id,code,name',
            'department:id,name',
            'requester:id,name'
        ])
            ->where('status', RequisitionStatus::PENDING->value)
            ->orderByDesc('id')
            ->paginate(20);

        return view('requisitions.inbox.validation', compact('rows'));
    }

    /**
     * Bandeja: requisiciones rechazadas.
     * Nota: No tienes campo 'rejection_reason' ni 'rejected_by' en la migración
     */
    public function rejectedInbox(Request $request)
    {
        $rows = Requisition::with([
            'company:id,name',
            'costCenter:id,code,name',
            'department:id,name',
            'requester:id,name'
            // No hay 'rejecter' porque no tienes el campo en la migración
        ])
            ->where('status', RequisitionStatus::REJECTED->value)
            ->orderByDesc('id')
            ->paginate(20);

        return view('requisitions.inbox.rejected', compact('rows'));
    }

    /**
     * Mostrar página de validación y cotización de requisición.
     */
    public function showValidationPage(Requisition $requisition)
    {
        // Verificar que la requisición esté en estado PENDING
        if ($requisition->status !== RequisitionStatus::PENDING) {
            return redirect()
                ->route('requisitions.inbox.validation')
                ->with('error', 'Solo se pueden validar requisiciones en estado PENDIENTE.');
        }

        // Cargar relaciones necesarias
        $requisition->load([
            'requester',
            'company',
            'costCenter',
            'department',
            'items.productService',
            'items.expenseCategory',
        ]);

        return view('requisitions.validate', compact('requisition'));
    }

    /**
     * Poner en espera con motivo.
     * RN-006: Compras puede pausar una requisición
     */
    public function hold(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // Solo se puede pausar si está enviada a Compras o ya pausada
        if (!in_array($requisition->status, [RequisitionStatus::PENDING->value, RequisitionStatus::PAUSED->value], true)) {
            return $this->respond($request, false, 'La requisición no puede ser pausada en su estado actual.');
        }

        $requisition->update([
            'status' => RequisitionStatus::PAUSED->value,
            'pause_reason' => $data['reason'],
            'paused_by' => Auth::id(),
            'paused_at' => now(),
        ]);

        return $this->respond($request, true, '⏸️ Requisición puesta en espera.');
    }

    /**
     * Reanudar una requisición en espera.
     */
    public function resume(Request $request, Requisition $requisition)
    {
        // Solo se puede reanudar si está pausada
        if ($requisition->status !== RequisitionStatus::PAUSED->value) {
            return $this->respond($request, false, 'La requisición no está pausada.');
        }

        // Volver al estado anterior (asumimos que viene de 'pending')
        $requisition->update([
            'status' => RequisitionStatus::PENDING->value,
            // Limpiar campos de pausa
            'pause_reason' => null,
            'paused_by' => null,
            'paused_at' => null,
            'reactivated_by' => Auth::id(),
            'reactivated_at' => now(),
        ]);

        return $this->respond($request, true, '▶️ Requisición reanudada.');
    }

    /**
     * Validar requisición y enviar a cotización.
     * El comprador valida que la requisición cumple con los parámetros necesarios
     * (especificaciones claras, tiempos de entrega viables, etc.) y la marca
     * como lista para iniciar el proceso de cotización.
     */
    public function validate(Request $request, Requisition $requisition)
    {
        // Idempotencia: si ya está en cotización, no hacer nada
        if ($requisition->status === RequisitionStatus::IN_QUOTATION) {
            return $this->respond($request, true, 'Esta requisición ya está en proceso de cotización.');
        }

        // Solo permitir validar desde PENDING (enviada a Compras)
        if ($requisition->status !== RequisitionStatus::PENDING) {
            return $this->respond($request, false, 'Solo se pueden validar requisiciones en estado Pendiente.');
        }

        // Cambiar estado a IN_QUOTATION
        $requisition->update([
            'status' => RequisitionStatus::IN_QUOTATION->value,
            'updated_by' => Auth::id(),
            'pause_reason' => null,
            'paused_by' => null,
            'paused_at' => null,
        ]);

        // Enviar notificación al requisitor
        if ($requisition->requester) {
            $requisition->requester->notify(new RequisitionInQuotationNotification($requisition));
        }

        return $this->respond($request, true, '✅ Requisición validada. Puede proceder con el proceso de cotización.');
    }

    /**
     * Rechazar requisición.
     * Misión: Procesar el rechazo usando la artillería pesada del modelo y avisar a las tropas.
     */
    public function reject(Request $request, Requisition $requisition)
    {
        // 1. Validación estricta: Si no hay un motivo de al menos 10 caracteres, no hay trámite.
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        // 2. Verificación de Estado: Consultamos al Enum si la transición es legal.
        if (!$requisition->canBeRejected()) {
            return $this->respond(
                $request,
                false,
                "Operación denegada. El estado actual ({$requisition->status->label()}) no permite el rechazo."
            );
        }

        try {
            // 3. Ejecutar el rechazo en el Modelo: Encapsulamos el cambio de status y los campos 'rejected_at/by'.
            $success = $requisition->reject(
                $data['rejection_reason'],
                Auth::id()
            );

            if (!$success) {
                throw new \Exception("El modelo Requisition se negó a procesar el rechazo.");
            }

            // 4. Registro de Auditoría: Spatie Activitylog deja huella de quién fue el verdugo.
            activity()
                ->performedOn($requisition)
                ->causedBy(Auth::user())
                ->withProperty('motivo', $data['rejection_reason'])
                ->log('Requisición rechazada por el departamento de compras');

            // 5. Notificación Automática: Avisamos al solicitante para que deje de esperar.
            if ($requisition->requester) {
                $requisition->requester->notify(new RequisitionRejectedNotification($requisition));
            }

            // 6. Respuesta Final: Misión cumplida.
            return $this->respond($request, true, '❌ Requisición rechazada. El solicitante ha sido notificado por correo y sistema.');
        } catch (\Exception $e) {
            // En caso de emboscada (error de SQL Server o red), reportamos el daño al Log.
            Log::error("Falla crítica en rechazo de requisición ID {$requisition->id}: " . $e->getMessage());

            return $this->respond(
                $request,
                false,
                'Error interno al procesar el rechazo. El Sargento ya fue informado.'
            );
        }
    }

    /**
     * Cancelar requisición.
     */
    public function cancel(Request $request, Requisition $requisition)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // Verificar que la requisición se pueda cancelar
        $allowedStatuses = [
            RequisitionStatus::DRAFT->value,
            RequisitionStatus::PENDING->value,
            RequisitionStatus::PAUSED->value,
            RequisitionStatus::APPROVED->value,
        ];

        if (!in_array($requisition->status, $allowedStatuses, true)) {
            return $this->respond($request, false, 'Esta requisición no puede ser cancelada en su estado actual.');
        }

        // Validar permisos según estado
        $user = Auth::user();
        $isRequester = $requisition->requested_by === $user->id; // Cambiado de requester_id a requested_by

        // Lógica de permisos simplificada sin 'approved_by'
        if ($requisition->status === RequisitionStatus::APPROVED->value) {
            // Después de aprobada, solo admin/superadmin pueden cancelar
            if (!$user->hasRole(['superadmin', 'admin'])) {
                return $this->respond($request, false, 'Solo administradores pueden cancelar una requisición aprobada.');
            }
        } elseif ($requisition->status === RequisitionStatus::DRAFT->value) {
            // Solo el requisitor puede cancelar un borrador
            if (!$isRequester && !$user->hasRole(['superadmin', 'admin'])) {
                return $this->respond($request, false, 'Solo el requisitor puede cancelar un borrador.');
            }
        }

        // Cancelar la requisición
        $requisition->update([
            'status' => RequisitionStatus::CANCELLED->value,
            'cancellation_reason' => $data['reason'],
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            // Limpiar campos de pausa si existían
            'pause_reason' => null,
            'paused_by' => null,
            'paused_at' => null,
        ]);

        // TODO: Notificar a involucrados
        // event(new RequisitionCancelled($requisition));

        return $this->respond($request, true, '🛑 Requisición cancelada.');
    }

    /**
     * Enviar requisición a Compras (desde borrador o pausada).
     * RN-005: El requisitor envía directamente a Compras (sin aprobación interna)
     */
    public function submitToApproval(Request $request, Requisition $requisition)
    {
        echo '<pre>';
        var_dump("Entré");
        die();
        // Solo se puede enviar desde borrador o pausada
        if (!$requisition->canBeSubmitted() && !$requisition->isPaused()) {
            return $this->respond($request, false, 'La requisición no puede ser enviada desde su estado actual.');
        }

        // Validar que tenga al menos una partida (RN-003)
        if ($requisition->items()->count() === 0) {
            return $this->respond($request, false, 'La requisición debe tener al menos una partida (RN-003).');
        }

        try {
            DB::beginTransaction();

            // Enviar a Compras
            $requisition->update([
                'status' => RequisitionStatus::PENDING,
                // Limpiar campos de pausa si venía de ahí
                'pause_reason' => null,
                'paused_by' => null,
                'paused_at' => null,
                'updated_by' => Auth::id(),
            ]);

            // 📧 Notificar al requisitor
            $requisition->requester->notify(new RequisitionSubmittedNotification($requisition));

            DB::commit();

            // TODO: Notificar a Compras
            // event(new RequisitionSubmitted($requisition));

            return $this->respond($request, true, '📤 Requisición enviada a Compras.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al enviar requisición a Compras', [
                'requisition_id' => $requisition->id,
                'error' => $e->getMessage()
            ]);

            return $this->respond($request, false, 'Error al enviar la requisición: ' . $e->getMessage());
        }
    }

    /* =========================================================================================
     * Helpers
     * ========================================================================================= */

    /**
     * Respuesta unificada para fetch/HTML.
     */
    private function respond(Request $request, bool $ok, string $msg)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $msg], $ok ? 200 : 422);
        }

        $level = $ok ? 'success' : ($msg[0] === '❌' || $msg[0] === '🛑' ? 'warning' : 'danger');
        return back()->with($level, $msg);
    }
}
