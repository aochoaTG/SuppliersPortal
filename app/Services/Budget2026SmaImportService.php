<?php

namespace App\Services;

class Budget2026SmaImportService extends Budget2026DgaImportService
{
    protected function configKey(): string
    {
        return 'budget_2026_sma';
    }
}
