<?php

namespace App\Providers;

use App\Database\Connectors\LegacySqlServerConnector;
use Illuminate\Support\ServiceProvider;

class SqlServerWorkaroundServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('db.connector.sqlsrv', fn () => new LegacySqlServerConnector);
    }
}
