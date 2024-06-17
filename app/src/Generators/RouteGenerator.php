<?php

namespace UmigameTech\Catapult\Generators;

use InvalidArgumentException;
use Newnakashima\TypedArray\Primitives;
use Newnakashima\TypedArray\TypedArray;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\FileSystem\FileSystemContainer;
use UmigameTech\Catapult\Templates\Renderer;

class RouteGenerator extends Generator
{
    const FOR_EVERYONE = ['all', 'everyone', 'any', '*'];

    public function __construct(Project $project, FileSystemContainer $container = null)
    {
        parent::__construct($project, $container);
    }

    protected function routesGrouping(Entity $entity, $indentLevel = 1)
    {
        if ($indentLevel === 0) {
            throw new InvalidArgumentException('indentLevel must be greater than 0');
        }

        return $this->indents($indentLevel)
            . "Route::prefix('{$entity->plural}/{{$entity->name}}')->name('{$entity->name}.')->group(function () {";
    }

    public function subActions(Entity $entity, $context = [], $forApi = false): TypedArray
    {
        if (empty($context)) {
            $context = [
                'prefix' => $entity->name,
                'indentLevel' => 1,
            ];
        }
        $actions = new TypedArray(Primitives::String->value);
        foreach ($entity->hasManyEntities as $childEntity) {
            $newPrefix = "{$context['prefix']}_{$childEntity->name}";
            $controllerName = $forApi ? $childEntity->apiControllerName() : $childEntity->controllerName();
            $controllerActions = $forApi
                ? ApiControllerGenerator::$apiActions
                : ControllerGenerator::$actions;
            foreach ($controllerActions as $actionName => $action) {
                // temporally skip actions other than index
                if ($actionName !== 'index') {
                    continue;
                }
                $methods = is_array($action['method']) ? $action['method'] : [$action['method']];
                $actionPath = empty($action['route']) ? '' : '/' . $action['route'];
                foreach ($methods as $method) {
                    $actions[] = $this->indents($context['indentLevel'] + 1)
                        . "Route::{$method}('{$childEntity->plural}{$actionPath}', "
                        . "[{$controllerName}::class, '{$newPrefix}_{$actionName}'])->name('{$childEntity->name}.{$actionName}');";
                }
            }

            $newContext = [
                'prefix' => $newPrefix,
                'indentLevel' => $context['indentLevel'] + 1,
            ];
            $actions = $actions->merge(
                $this->subActions($childEntity, $newContext, $forApi)
            );
        }

        if ($actions->count() > 0) {
            $actions->unshift($this->routesGrouping($entity, $context['indentLevel']));
            $actions->push($this->indents($context['indentLevel']) . '});');
        }

        return $actions;
    }

    protected function convertActionName(Entity $entity, int $indentLevel = 0, Entity $parent = null)
    {
        $converted = [];
        $plural = $this->inflector->pluralize($entity->name);
        $actions = ControllerGenerator::$actions;
        $controllerName = $entity->controllerName();
        foreach ($actions as $actionName => $action) {
            $methods = is_array($action['method']) ? $action['method'] : [$action['method']];
            $actionPath = empty($action['route']) ? '' : '/' . $action['route'];
            foreach ($methods as $method) {
                $converted[] = $this->indents($indentLevel)
                    . "Route::{$method}('{$plural}{$actionPath}', "
                    . "[{$controllerName}::class, '{$actionName}'])->name('{$entity->name}.{$actionName}');";
            }
        }

        // TODO: indentLevelじゃなくてparentを使いたい。。
        if ($entity->isAuthenticatable() && $indentLevel === 1) {
            $converted[] = $this->indents($indentLevel) . "Route::get('dashboard', "
                . "[{$controllerName}::class, 'dashboard'])->name('{$plural}.dashboard');";
            $converted[] = $this->indents($indentLevel)
                . "Route::get('/', fn () => redirect()->route('{$plural}.dashboard'));";
        }

        // relationがなければここで終了
        if (! $entity->hasHasManyEntities()) {
            return $converted;
        }

        $converted = array_merge($converted, $this->subActions($entity)->toArray());

        return $converted;
    }

    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $webRoutePath = $this->projectPath() . '/routes/web.php';

        $authList = $this->makeAuthList();

        $forEveryone = $this->routesForEveryone();

        $loginRoutes = [];
        $this->entities->filter(fn (Entity $entity) => $entity->isAuthenticatable())
            ->each(function (Entity $entity) use (&$loginRoutes) {
                $authName = $entity->authName();
                $controllerName = $entity->controllerName();
                $loginRoutes[] = "Route::get('{$authName}/login', [{$controllerName}::class, 'login'])->name('{$authName}.login');";
                $loginRoutes[] = "Route::post('{$authName}/login', [{$controllerName}::class, 'loginSubmit'])->name('{$authName}.loginSubmit');";
                $loginRoutes[] = "Route::delete('{$authName}/logout', [{$controllerName}::class, 'logout'])->name('{$authName}.logout');";
            });

        $routes = $renderer->render('routes/web.php.twig', [
            'authList' => $authList,
            'forEveryone' => $forEveryone,
            'entities' => $this->entities,
            'loginRoutes' => $loginRoutes,
        ]);

        return [
            'path' => $webRoutePath,
            'content' => $routes,
        ];
    }

    protected function makeAuthList()
    {
        $authNames = $this->entities
            ->filter(fn (Entity $entity) => $entity->isAuthenticatable())
            ->map(fn (Entity $entity) => $this->inflector->pluralize($entity->name));
        $authList = [];
        foreach ($authNames as $authName) {
            $filtered = $this->entities->filter(
                function (Entity $entity) use ($authName) {
                    $allowedFor = array_map(
                        fn ($allowed) => $this->inflector->pluralize($allowed),
                        $entity->allowedFor
                    );
                    return in_array($authName, $allowedFor);
                }
            );

            $authList[$authName] = $this->convertEntitiesForRoute($filtered, 1);
        }

        return $authList;
    }

    protected function routesForEveryone()
    {
        $filtered = $this->entities->filter(
            function ($entity) {
                foreach ($entity->allowedFor as $allowed) {
                    if (in_array($allowed, self::FOR_EVERYONE)) {
                        return true;
                    }
                }

                return false;
            }
        );

        return $this->convertEntitiesForRoute($filtered);
    }

    protected function convertEntitiesForRoute(TypedArray $entities, int $indentLevel = 0)
    {
        $entities = $entities->map(
            function (Entity $entity) use ($indentLevel) {
                $routes = $this->convertActionName($entity, $indentLevel);
                $entity->routes = $routes;
                return $entity;
            }
        );

        return $entities;
    }

    public function generate()
    {
        $content = $this->generateContent();
        $this->writer->write(...$content);
    }
}
