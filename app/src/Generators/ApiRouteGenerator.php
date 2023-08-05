<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ApiRouteGenerator extends RouteGenerator
{
    protected function convertActionName(Entity $entity, int $indentLevel = 0, Entity $parent = null)
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

        $converted[] = $this->routesGrouping($entity, $indentLevel + 1);
        foreach ($entity->hasManyEntities as $subEntity) {
            $converted = array_merge($converted, $this->subActions(entity: $subEntity, forApi: true)->toArray());
        }
        $converted[] = '});';

        return $converted;
    }

    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $apiRoutePath = $this->projectPath() . '/routes/api.php';

        $forEveryone = $this->routesForEveryone();

        $routes = $renderer->render('routes/api.php.twig', [
            'forEveryone' => $forEveryone,
            'entities' => $this->entities,
        ]);

        return [
            'path' => $apiRoutePath,
            'content' => $routes,
        ];
    }
}
