<?php

namespace App\Enum;

/**
 * RequisitionStatus
 * Flujo flexible con estados adicionales para cubrir escenarios comunes.
 * - draft:     borrador
 * - in_review: en revisión
 * - on_hold:   en espera (p. ej. info faltante)
 * - budget_exception: requiere autorización por techo
 * - approved:  aprobada (Fase 4: COMMITMENT)
 * - rejected:  rechazada (Fase 4: RELEASE si hubo compromiso)
 * - cancelled: cancelada (Fase 4: RELEASE si hubo compromiso)
 * - partially_received: recepción parcial (si manejas recepción)
 * - received:  recibida (lista para facturar)
 * - partially_invoiced: facturada parcialmente
 * - invoiced:  facturada (Fase 4: puede disparar CONSUMPTION)
 * - closed:    cerrada (flujo final)
 */
enum RequisitionStatus: string
{
    case DRAFT = 'draft';
    case IN_REVIEW = 'in_review';
    case ON_HOLD = 'on_hold';
    case BUDGET_EXCEPTION = 'budget_exception';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case PARTIALLY_RECEIVED = 'partially_received';
    case RECEIVED = 'received';
    case PARTIALLY_INVOICED = 'partially_invoiced';
    case INVOICED = 'invoiced';
    case CLOSED = 'closed';

    public static function options(): array
    {
        return [
            self::DRAFT->value => 'Borrador',
            self::IN_REVIEW->value => 'En revisión',
            self::ON_HOLD->value => 'En espera',
            self::BUDGET_EXCEPTION->value => 'Excepción de presupuesto',
            self::APPROVED->value => 'Aprobada',
            self::REJECTED->value => 'Rechazada',
            self::CANCELLED->value => 'Cancelada',
            self::PARTIALLY_RECEIVED->value => 'Recepción parcial',
            self::RECEIVED->value => 'Recibida',
            self::PARTIALLY_INVOICED->value => 'Facturación parcial',
            self::INVOICED->value => 'Facturada',
            self::CLOSED->value => 'Cerrada',
        ];
    }

    /**
     * Sugerencia de color Bootstrap para badge.
     */
    public static function badgeClass(self $status): string
    {
        return match ($status) {
            self::DRAFT => 'secondary',
            self::IN_REVIEW => 'info',
            self::ON_HOLD => 'warning',
            self::BUDGET_EXCEPTION => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'dark',
            self::PARTIALLY_RECEIVED => 'primary',
            self::RECEIVED => 'primary',
            self::PARTIALLY_INVOICED => 'primary',
            self::INVOICED => 'primary',
            self::CLOSED => 'success',
        };
    }
}
