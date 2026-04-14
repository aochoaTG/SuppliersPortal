<?php

/**
 * Route Usage Analyzer for SuppliersPortal
 * 
 * This script analyzes web.php routes and verifies:
 * 1. Controller methods exist
 * 2. Views referenced exist
 * 3. Potential issues (duplicates, orphaned routes)
 * 
 * Run: php analyze-routes.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Str;

class RouteAnalyzer
{
    private array $routes = [];
    private array $issues = [];
    private array $controllerMethods = [];
    private array $views = [];
    
    public function __construct()
    {
        $this->loadControllerMethods();
        $this->parseRoutesFile();
    }
    
    private function loadControllerMethods(): void
    {
        $controllerDir = __DIR__ . '/app/Http/Controllers';
        $files = glob($controllerDir . '/*Controller.php');
        
        foreach ($files as $file) {
            $className = 'App\\Http\\Controllers\\' . basename($file, '.php');
            if (class_exists($className)) {
                $methods = get_class_methods($className);
                if ($methods) {
                    $this->controllerMethods[$className] = $methods;
                }
            }
        }
        
        // Also check subdirectories
        $subdirs = glob($controllerDir . '/*', GLOB_ONLYDIR);
        foreach ($subdirs as $subdir) {
            $files = glob($subdir . '/*Controller.php');
            foreach ($files as $file) {
                $namespace = str_replace(__DIR__ . '/app/Http/Controllers/', '', $subdir);
                $namespace = 'App\\Http\\Controllers\\' . str_replace('/', '\\', $namespace);
                $className = $namespace . '\\' . basename($file, '.php');
                if (class_exists($className)) {
                    $methods = get_class_methods($className);
                    if ($methods) {
                        $this->controllerMethods[$className] = $methods;
                    }
                }
            }
        }
    }
    
    private function parseRoutesFile(): void
    {
        $content = file_get_contents(__DIR__ . '/routes/web.php');
        
        // Extract routes with controller@method
        preg_match_all(
            '/Route::(?:get|post|put|patch|delete|resource)\([\'"]([^\'"]+)[\'"].*?\[?([^\s\]]+?)::class,\s*[\'"]?(\w+)[\'"]?\)/s',
            $content,
            $matches,
            PREG_SET_ORDER
        );
        
        foreach ($matches as $match) {
            $this->routes[] = [
                'path' => $match[1],
                'controller' => $match[2],
                'method' => $match[3],
            ];
        }
        
        // Extract inline closures that return views
        preg_match_all(
            '/view\([\'"]([^\'"]+)[\'"]\)/',
            $content,
            $viewMatches
        );
        
        $this->views = array_unique($viewMatches[1]);
    }
    
    public function analyze(): array
    {
        $report = [
            'total_routes' => count($this->routes),
            'missing_methods' => [],
            'existing_methods' => 0,
            'views_referenced' => $this->views,
            'missing_views' => [],
            'controller_usage' => [],
        ];
        
        // Check each route
        foreach ($this->routes as $route) {
            $controllerClass = $route['controller'];
            $method = $route['method'];
            
            // Track controller usage
            if (!isset($report['controller_usage'][$controllerClass])) {
                $report['controller_usage'][$controllerClass] = 0;
            }
            $report['controller_usage'][$controllerClass]++;
            
            // Check if method exists
            if (isset($this->controllerMethods[$controllerClass])) {
                if (in_array($method, $this->controllerMethods[$controllerClass])) {
                    $report['existing_methods']++;
                } else {
                    $report['missing_methods'][] = [
                        'route' => $route['path'],
                        'controller' => $controllerClass,
                        'method' => $method,
                    ];
                }
            } else {
                $report['missing_methods'][] = [
                    'route' => $route['path'],
                    'controller' => $controllerClass,
                    'method' => $method,
                    'issue' => 'Controller class not found',
                ];
            }
        }
        
        // Check views
        foreach ($this->views as $view) {
            $viewPath = __DIR__ . '/resources/views/' . str_replace('.', '/', $view) . '.blade.php';
            if (!file_exists($viewPath)) {
                $report['missing_views'][] = [
                    'view' => $view,
                    'path' => $viewPath,
                ];
            }
        }
        
        return $report;
    }
    
    public function printReport(array $report): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║         ROUTE USAGE ANALYSIS REPORT                         ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        
        echo "📊 SUMMARY\n";
        echo "─────────────────────────────────────────────────────────────────\n";
        echo "Total routes analyzed: {$report['total_routes']}\n";
        echo "Valid controller methods: {$report['existing_methods']}\n";
        echo "Missing methods: " . count($report['missing_methods']) . "\n";
        echo "Views referenced: " . count($report['views_referenced']) . "\n";
        echo "Missing views: " . count($report['missing_views']) . "\n";
        echo "\n";
        
        if (!empty($report['missing_methods'])) {
            echo "❌ MISSING CONTROLLER METHODS\n";
            echo "─────────────────────────────────────────────────────────────────\n";
            foreach ($report['missing_methods'] as $issue) {
                echo "Route: {$issue['route']}\n";
                echo "  → {$issue['controller']}::{$issue['method']}()\n";
                if (isset($issue['issue'])) {
                    echo "  ⚠️ {$issue['issue']}\n";
                }
                echo "\n";
            }
        }
        
        if (!empty($report['missing_views'])) {
            echo "❌ MISSING VIEWS\n";
            echo "─────────────────────────────────────────────────────────────────\n";
            foreach ($report['missing_views'] as $issue) {
                echo "View: {$issue['view']}\n";
                echo "  Path: {$issue['path']}\n\n";
            }
        }
        
        echo "📈 CONTROLLER USAGE\n";
        echo "─────────────────────────────────────────────────────────────────\n";
        arsort($report['controller_usage']);
        foreach ($report['controller_usage'] as $controller => $count) {
            $shortName = class_basename($controller);
            echo str_pad($shortName, 45) . " → {$count} routes\n";
        }
        echo "\n";
    }
}

// Run analysis
$analyzer = new RouteAnalyzer();
$report = $analyzer->analyze();
$analyzer->printReport($report);

// Save detailed report
file_put_contents(
    __DIR__ . '/storage/logs/route-analysis-' . date('Y-m-d') . '.json',
    json_encode($report, JSON_PRETTY_PRINT)
);

echo "✅ Detailed report saved to: storage/logs/route-analysis-" . date('Y-m-d') . ".json\n\n";
