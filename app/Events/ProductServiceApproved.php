<?php

namespace App\Events;

use App\Models\ProductService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento que se dispara cuando un producto/servicio es aprobado
 *
 * PASO 3E - Crear en: app/Events/ProductServiceApproved.php
 */
class ProductServiceApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ProductService $productService;

    /**
     * Constructor
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
}
