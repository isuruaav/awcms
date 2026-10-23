<?php

// Boot the local console kernel and inspect metadata only. No HTTP requests,
// database queries, migrations, component mount methods, or application writes.
declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

$root = dirname(__DIR__, 3);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$rows = [];
$relative = static fn (string $path): string => str_replace('\\', '/', str_starts_with($path, $root) ? substr($path, strlen($root) + 1) : $path);
foreach ($app['router']->getRoutes() as $route) {
    $uses = $route->getAction('uses');
    $component = $route->getAction('livewire_component');
    $row = ['name' => $route->getName(), 'uri' => $route->uri(), 'methods' => $route->methods(), 'constraints' => $route->wheres, 'defaults' => $route->defaults, 'component' => is_string($component) ? $component : null, 'handler' => is_string($uses) ? $uses : 'Closure', 'source' => null, 'line' => null, 'resolved_component' => null, 'component_source' => null, 'error' => null];
    try {
        if ($uses instanceof Closure) {
            $reflection = new ReflectionFunction($uses);
            $row['source'] = $relative($reflection->getFileName());
            $row['line'] = $reflection->getStartLine();
        } elseif (is_string($uses)) {
            [$class, $method] = array_pad(explode('@', $uses, 2), 2, '__invoke');
            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $row['source'] = $relative($reflection->getFileName());
                if ($reflection->hasMethod($method)) {
                    $row['line'] = $reflection->getMethod($method)->getStartLine();
                }
            }
        }
        if (is_string($component)) {
            $resolved = $app['livewire.factory']->resolveComponentClass($component);
            $row['resolved_component'] = $resolved;
            $row['component_source'] = $relative((new ReflectionClass($resolved))->getFileName());
        }
    } catch (Throwable $error) {
        $row['error'] = $error->getMessage();
    }
    $rows[] = $row;
}
$configuration = [];
foreach (['app.env', 'app.debug', 'app.url', 'awcms.active_theme', 'database.default', 'cache.default', 'session.driver', 'queue.default', 'filesystems.default', 'images.default'] as $key) {
    $configuration[$key] = config($key);
}
$models = [];
foreach (glob($root.'/app/Models/*.php') as $file) {
    $class = 'App\\Models\\'.basename($file, '.php');
    if (class_exists($class) && is_subclass_of($class, Model::class)) {
        $model = new $class;
        $models[] = ['class' => $class, 'table' => $model->getTable(), 'key' => $model->getKeyName(), 'soft_deletes' => in_array(SoftDeletes::class, class_uses_recursive($class)), 'fillable' => $model->getFillable(), 'casts' => $model->getCasts()];
    }
}
$target = dirname(__DIR__).'/runtime-metadata.json';
file_put_contents($target, json_encode(['routes' => $rows, 'configuration' => $configuration, 'models' => $models], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo json_encode(['routes' => count($rows), 'models' => count($models), 'resolution_errors' => array_values(array_filter($rows, fn ($row) => $row['error'] !== null))], JSON_UNESCAPED_SLASHES).PHP_EOL;
