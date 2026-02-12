<?php

namespace App\Services;

use App\Models\ApprovalLevel;
use Illuminate\Support\Facades\Cache;

class ApprovalService
{
    /**
     * Obtiene la colección completa de niveles (Desde Cache o DB)
     * Este es el método que usará tu controlador para la vista index.
     */
    public function getAllLevels()
    {
        return Cache::remember('approval_levels_list', 3600, function () {
            return ApprovalLevel::orderBy('level_number', 'asc')->get();
        });
    }

    /**
     * Determina el nivel de aprobación basado en el monto total.
     * Reutiliza getAllLevels para mantener la consistencia del cache.
     */
    public function getLevelForAmount($amount)
    {
        $levels = $this->getAllLevels();

        return $levels->first(function ($level) use ($amount) {
            return $amount >= $level->min_amount &&
                (is_null($level->max_amount) || $amount <= $level->max_amount);
        });
    }

    /**
     * Limpia el cache cuando se actualizan los niveles en el Panel de Configuración.
     * ¡No olvides llamarlo en el ApprovalLevelController@update!
     */
    public function clearCache()
    {
        Cache::forget('approval_levels_list');
    }
}
