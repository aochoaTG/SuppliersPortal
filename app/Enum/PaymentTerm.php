<?php

namespace App\Enum;

/**
 * Condiciones de Pago para Órdenes de Compra y Proveedores
 */
enum PaymentTerm: string
{
    case CASH = 'CASH';
    case IMMEDIATE = 'IMMEDIATE';
    case UPFRONT_100 = 'UPFRONT_100';
    case UPFRONT_50_DELIVERY_50 = 'UPFRONT_50_DELIVERY_50';
    case UPFRONT_30_DELIVERY_70 = 'UPFRONT_30_DELIVERY_70';
    case UPFRONT_20_DELIVERY_80 = 'UPFRONT_20_DELIVERY_80';
    case UPFRONT_50_INVOICE_50 = 'UPFRONT_50_INVOICE_50';
    case COD = 'COD'; // Cash on Delivery
    case NET_7 = 'NET_7';
    case NET_10 = 'NET_10';
    case NET_15 = 'NET_15';
    case NET_21 = 'NET_21';
    case NET_30 = 'NET_30';
    case NET_45 = 'NET_45';
    case NET_60 = 'NET_60';
    case NET_90 = 'NET_90';
    case NET_120 = 'NET_120';
    case EOM = 'EOM'; // End of Month
    case EOM_15 = 'EOM_15'; // End of Month + 15 days
    case INVOICE_RECEIPT = 'INVOICE_RECEIPT';
    case CURRENT_MONTH = 'CURRENT_MONTH';
    case WIRE_TRANSFER = 'WIRE_TRANSFER';
    case NOMINATIVE_CHECK = 'NOMINATIVE_CHECK';
    case CORPORATE_CARD = 'CORPORATE_CARD';
    case FACTORING = 'FACTORING';

    /**
     * Obtiene las opciones para selects en español
     */
    public static function options(): array
    {
        return [
            self::CASH->value => 'Contado',
            self::IMMEDIATE->value => 'Pago Inmediato',
            self::UPFRONT_100->value => 'Pago Anticipado (100%)',
            self::UPFRONT_50_DELIVERY_50->value => '50% Anticipo - 50% Entrega',
            self::UPFRONT_30_DELIVERY_70->value => '30% Anticipo - 70% Entrega',
            self::UPFRONT_20_DELIVERY_80->value => '20% Anticipo - 80% Entrega',
            self::UPFRONT_50_INVOICE_50->value => '50% Anticipo - 50% Contra Factura',
            self::COD->value => 'Contra Entrega',
            self::NET_7->value => 'Crédito 7 días',
            self::NET_10->value => 'Crédito 10 días',
            self::NET_15->value => 'Crédito 15 días',
            self::NET_21->value => 'Crédito 21 días',
            self::NET_30->value => 'Crédito 30 días',
            self::NET_45->value => 'Crédito 45 días',
            self::NET_60->value => 'Crédito 60 días',
            self::NET_90->value => 'Crédito 90 días',
            self::NET_120->value => 'Crédito 120 días',
            self::EOM->value => 'Fin de Mes',
            self::EOM_15->value => 'Fin de Mes + 15 días',
            self::INVOICE_RECEIPT->value => 'A la recepción de factura',
            self::CURRENT_MONTH->value => 'Mes Corriente',
            self::WIRE_TRANSFER->value => 'Transferencia Bancaria',
            self::NOMINATIVE_CHECK->value => 'Cheque Nominativo',
            self::CORPORATE_CARD->value => 'Tarjeta de Crédito Corporativa',
            self::FACTORING->value => 'Factoraje Financiero',
        ];
    }

    /**
     * Obtiene el label para mostrar en la UI
     */
    public function label(): string
    {
        return self::options()[$this->value];
    }
}
