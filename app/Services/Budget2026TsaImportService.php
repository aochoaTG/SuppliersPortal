<?php

namespace App\Services;

class Budget2026TsaImportService extends Budget2026DgaImportService
{
    protected function configKey(): string
    {
        return 'budget_2026_tsa';
    }
}
