<?php

namespace App\Events;

use App\Models\Requisition;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequisitionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The requisition instance.
     *
     * @var \App\Models\Requisition
     */
    public $requisition;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Requisition  $requisition
     * @return void
     */
    public function __construct(Requisition $requisition)
    {
        $this->requisition = $requisition;
    }
}
