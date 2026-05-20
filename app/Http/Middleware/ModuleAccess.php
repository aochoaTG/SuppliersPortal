<?php

namespace App\Http\Middleware;

use App\Services\ModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleAccess
{
    public function __construct(
        private readonly ModuleAccessService $moduleAccessService
    ) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless(
            $this->moduleAccessService->userCanAccessModule($request->user(), $module),
            403
        );

        return $next($request);
    }
}
