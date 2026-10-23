<?php

// Read-only source inspection. Never boot the application or load .env values.
declare(strict_types=1);
require dirname(__DIR__, 3).'/vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

$root = dirname(__DIR__, 3);
$parser = (new ParserFactory)->createForNewestSupportedVersion();
$printer = new Standard;
$finder = new NodeFinder;
$output = [];
$paths = [];
foreach (['app', 'bootstrap', 'config', 'database', 'routes', 'tests', 'resources/views'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (! $file->isFile() || $file->getExtension() !== 'php' || str_starts_with($relative, 'bootstrap/cache/')) {
            continue;
        }
        if (str_ends_with($relative, '.blade.php') && ! str_contains(file_get_contents($file->getPathname()), '<?php')) {
            continue;
        }
        $paths[$relative] = $file->getPathname();
    }
}
ksort($paths);
function scalarValue(mixed $node): mixed
{
    if ($node instanceof Node\Scalar\String_ || $node instanceof Node\Scalar\Int_ || $node instanceof Node\Scalar\Float_) {
        return $node->value;
    }
    if ($node instanceof Node\Expr\ClassConstFetch && $node->class instanceof Node\Name && $node->name instanceof Node\Identifier) {
        return ['class' => $node->class->toString(), 'constant' => $node->name->toString()];
    }
    if ($node instanceof Node\Expr\ConstFetch) {
        return ['constant' => $node->name->toString()];
    }
    if ($node instanceof Node\Expr\Array_) {
        $values = [];
        foreach ($node->items as $item) {
            if ($item !== null) {
                $values[] = ['key' => scalarValue($item->key), 'value' => scalarValue($item->value)];
            }
        }

        return $values;
    }

    return null;
}
function sourceRoutes(mixed $node, string $prefix = '', string $namePrefix = ''): array
{
    $found = [];
    if (is_array($node)) {
        foreach ($node as $child) {
            $found = array_merge($found, sourceRoutes($child, $prefix, $namePrefix));
        }

        return $found;
    }
    if (! $node instanceof Node) {
        return [];
    }
    if ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\StaticCall) {
        $chain = [];
        $root = $node;
        while ($root instanceof Node\Expr\MethodCall) {
            array_unshift($chain, $root);
            $root = $root->var;
        }
        if ($root instanceof Node\Expr\StaticCall && $root->class instanceof Node\Name && $root->class->toString() === 'Illuminate\\Support\\Facades\\Route' && $root->name instanceof Node\Identifier) {
            array_unshift($chain, $root);
            $rootMethod = $root->name->toString();
            $localPrefix = '';
            $localName = '';
            foreach ($chain as $call) {
                if (! $call->name instanceof Node\Identifier) {
                    continue;
                }
                $method = $call->name->toString();
                $value = isset($call->args[0]) ? scalarValue($call->args[0]->value) : null;
                if ($method === 'prefix' && is_string($value)) {
                    $localPrefix = $value;
                }
                if ($method === 'name' && is_string($value)) {
                    $localName = $value;
                }
            }
            foreach ($chain as $call) {
                if (! $call->name instanceof Node\Identifier || $call->name->toString() !== 'group') {
                    continue;
                }
                foreach ($call->args as $argument) {
                    if ($argument->value instanceof Node\Expr\Closure) {
                        return sourceRoutes($argument->value->stmts, trim($prefix.'/'.$localPrefix, '/'), $namePrefix.$localName);
                    }
                }
            }
            if (in_array($rootMethod, ['get', 'post', 'put', 'patch', 'delete', 'livewire', 'redirect', 'view', 'fallback'], true)) {
                $args = array_map(fn ($arg) => scalarValue($arg->value), $root->args);

                return [['method' => $rootMethod, 'uri' => $rootMethod === 'fallback' ? null : trim(trim($prefix, '/').'/'.trim(is_string($args[0] ?? null) ? $args[0] : '', '/'), '/'), 'name' => $localName !== '' ? $namePrefix.$localName : null, 'line' => $root->getStartLine(), 'arguments' => $args]];
            }
        }
    }
    foreach ($node->getSubNodeNames() as $key) {
        $found = array_merge($found, sourceRoutes($node->$key, $prefix, $namePrefix));
    }

    return $found;
}
foreach ($paths as $relative => $path) {
    $record = ['path' => $relative, 'classes' => [], 'imports' => [], 'references' => [], 'calls' => [], 'strings' => [], 'schema' => [], 'relations' => [], 'route_declarations' => [], 'error' => null];
    try {
        $nodes = $parser->parse(file_get_contents($path)) ?? [];
        $traverser = new NodeTraverser(new NameResolver);
        $nodes = $traverser->traverse($nodes);
        if (str_starts_with($relative, 'routes/')) {
            $record['route_declarations'] = sourceRoutes($nodes);
        }
        foreach ($finder->findInstanceOf($nodes, Node\Stmt\Use_::class) as $use) {
            foreach ($use->uses as $item) {
                $record['imports'][] = ['name' => $item->name->toString(), 'alias' => $item->getAlias()->toString(), 'line' => $item->getStartLine()];
            }
        }
        foreach ($finder->findInstanceOf($nodes, Node\Stmt\ClassLike::class) as $class) {
            $name = isset($class->namespacedName) ? $class->namespacedName->toString() : ($class->name?->toString() ?? '(anonymous)');
            $methods = [];
            foreach ($class->getMethods() as $method) {
                $parameters = [];
                foreach ($method->params as $parameter) {
                    $parameters[] = ($parameter->type ? $printer->prettyPrint([$parameter->type]).' ' : '').'$'.(is_string($parameter->var->name) ? $parameter->var->name : '?');
                }
                $methods[] = ['name' => $method->name->toString(), 'line' => $method->getStartLine(), 'visibility' => $method->isPrivate() ? 'private' : ($method->isProtected() ? 'protected' : 'public'), 'static' => $method->isStatic(), 'parameters' => $parameters, 'return_type' => $method->returnType ? $printer->prettyPrint([$method->returnType]) : null];
                foreach ($finder->findInstanceOf($method->stmts ?? [], Node\Expr\MethodCall::class) as $call) {
                    if ($call->var instanceof Node\Expr\Variable && $call->var->name === 'this' && $call->name instanceof Node\Identifier && in_array($call->name->toString(), ['belongsTo', 'hasMany', 'hasOne', 'belongsToMany', 'morphTo', 'morphMany', 'morphOne', 'morphToMany'], true)) {
                        $record['relations'][] = ['class' => $name, 'method' => $method->name->toString(), 'type' => $call->name->toString(), 'arguments' => array_map(fn ($arg) => scalarValue($arg->value), $call->args), 'line' => $call->getStartLine()];
                    }
                }
            }
            $properties = [];
            foreach ($class->getProperties() as $property) {
                foreach ($property->props as $item) {
                    $properties[$item->name->toString()] = scalarValue($item->default);
                }
            }
            $constants = [];
            foreach ($class->getConstants() as $constant) {
                foreach ($constant->consts as $item) {
                    $constants[$item->name->toString()] = scalarValue($item->value);
                }
            }
            $cases = [];
            if ($class instanceof Node\Stmt\Enum_) {
                foreach ($class->stmts as $item) {
                    if ($item instanceof Node\Stmt\EnumCase) {
                        $cases[$item->name->toString()] = scalarValue($item->expr);
                    }
                }
            }
            $record['classes'][] = ['name' => $name, 'kind' => $class instanceof Node\Stmt\Enum_ ? 'enum' : ($class instanceof Node\Stmt\Trait_ ? 'trait' : 'class'), 'line' => $class->getStartLine(), 'extends' => $class instanceof Node\Stmt\Class_ && $class->extends ? $class->extends->toString() : null, 'methods' => $methods, 'properties' => $properties, 'constants' => $constants, 'cases' => $cases];
        }
        foreach ($finder->findInstanceOf($nodes, Node\Name::class) as $name) {
            $value = $name->toString();
            if (preg_match('/^(App|Database|Tests)\\\\/', $value)) {
                $record['references'][] = ['name' => $value, 'line' => $name->getStartLine()];
            }
        }
        foreach ($finder->findInstanceOf($nodes, Node\Scalar\String_::class) as $string) {
            $record['strings'][] = ['value' => $string->value, 'line' => $string->getStartLine()];
        }
        foreach ($finder->find($nodes, fn ($n) => $n instanceof Node\Expr\FuncCall || $n instanceof Node\Expr\MethodCall || $n instanceof Node\Expr\StaticCall) as $call) {
            $name = $call->name instanceof Node\Name || $call->name instanceof Node\Identifier ? $call->name->toString() : '(dynamic)';
            $receiver = $call instanceof Node\Expr\StaticCall && $call->class instanceof Node\Name ? $call->class->toString() : ($call instanceof Node\Expr\MethodCall && $call->var instanceof Node\Expr\Variable && is_string($call->var->name) ? '$'.$call->var->name : '');
            $args = array_map(fn ($arg) => $arg instanceof Node\Arg ? scalarValue($arg->value) : null, $call->args);
            $record['calls'][] = ['kind' => $call instanceof Node\Expr\FuncCall ? 'function' : ($call instanceof Node\Expr\StaticCall ? 'static' : 'method'), 'receiver' => $receiver, 'name' => $name, 'arguments' => $args, 'line' => $call->getStartLine()];
            if ($call instanceof Node\Expr\StaticCall && $receiver === 'Illuminate\\Support\\Facades\\Schema' && in_array($name, ['create', 'table', 'dropIfExists', 'drop', 'hasTable', 'hasColumn', 'rename'], true)) {
                $schema = ['operation' => $name, 'table' => $args[0] ?? null, 'line' => $call->getStartLine(), 'columns' => []];
                foreach ($call->args as $arg) {
                    if (! $arg->value instanceof Node\Expr\Closure) {
                        continue;
                    }
                    foreach ($finder->findInstanceOf($arg->value->stmts, Node\Expr\MethodCall::class) as $column) {
                        if (! $column->var instanceof Node\Expr\Variable || $column->var->name !== 'table' || ! $column->name instanceof Node\Identifier) {
                            continue;
                        }
                        $schema['columns'][] = ['type' => $column->name->toString(), 'arguments' => array_map(fn ($a) => scalarValue($a->value), $column->args), 'line' => $column->getStartLine()];
                    }
                }
                $record['schema'][] = $schema;
            }
        }
    } catch (Throwable $error) {
        $record['error'] = $error->getMessage();
    }
    $output[] = $record;
}
$target = dirname(__DIR__).'/php-source.json';
file_put_contents($target, json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
echo json_encode(['parsed_files' => count($output), 'errors' => array_values(array_map(fn ($x) => ['path' => $x['path'], 'error' => $x['error']], array_filter($output, fn ($x) => $x['error'] !== null))), 'output' => $target], JSON_UNESCAPED_SLASHES).PHP_EOL;
