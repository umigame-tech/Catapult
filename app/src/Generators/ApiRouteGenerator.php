<?php

namespace UmigameTech\Catapult\Generators;

use Newnakashima\TypedArray\TypedArray;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ApiRouteGenerator extends RouteGenerator
{
    protected function convertActionName(Entity $entity, int $indentLevel = 0, Entity $authenticatableEntity = null)
    {
        $converted = [];
        $plural = $this->inflector->pluralize($entity->name);
        foreach (ApiControllerGenerator::$apiActions as $actionName => $action) {
            $methods = is_array($action['method']) ? $action['method'] : [$action['method']];
            $actionPath = empty($action['route']) ? '' : '/' . $action['route'];
            foreach ($methods as $method) {
                $converted[] = $this->indents($indentLevel)
                    . "Route::{$method}('{$plural}{$actionPath}', "
                    . "[{$entity->apiControllerName()}::class, '{$actionName}'])->name('{$entity->name}.{$actionName}');";
            }
        }

        // relationがなければここで終了
        if (! $entity->hasHasManyEntities()) {
            return $converted;
        }

        $converted = array_merge($converted, $this->subActions(entity: $entity, forApi: true)->toArray());

        return $converted;
    }

    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $apiRoutePath = $this->projectPath() . '/routes/api.php';

        $authList = $this->makeAuthList();

        $forEveryone = $this->routesForEveryone();
        $loginRoutes = [];
        $this->entities->filter(fn (Entity $entity) => $entity->isAuthenticatable())
            ->each(function (Entity $entity) use (&$loginRoutes) {
                $authName = $entity->authName();
                $controllerName = $entity->apiControllerName();
                $loginRoutes[] = "Route::get('{$authName}/login', [{$controllerName}::class, 'login'])->name('{$authName}.login');";
                $loginRoutes[] = "Route::post('{$authName}/login', [{$controllerName}::class, 'loginSubmit'])->name('{$authName}.loginSubmit');";
                $loginRoutes[] = "Route::delete('{$authName}/logout', [{$controllerName}::class, 'logout'])->name('{$authName}.logout');";
            });

        $routes = $renderer->render('routes/api.php.twig', [
            'authList' => $authList,
            'forEveryone' => $forEveryone,
            'entities' => $this->entities,
            'loginRoutes' => $loginRoutes,
        ]);

        return [
            'path' => $apiRoutePath,
            'content' => $routes,
        ];
    }

    protected function convertEntitiesForRoute(TypedArray $entities, $indentLevel = 0, Entity $authenticatableEntity = null)
    {
        $entities = $entities->map(
            function (Entity $entity) use ($indentLevel, $authenticatableEntity) {
                $routes = $this->convertActionName($entity, $indentLevel, $authenticatableEntity);
                $entity->routes = $routes;
                return $entity;
            }
        );

        return $entities;
    }
}
