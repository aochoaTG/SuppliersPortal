<?php

namespace App\Services;

class Budget2026GasomexImportService extends Budget2026DgaImportService
{
    protected function configKey(): string
    {
        return 'budget_2026_gasomex';
    }
}
