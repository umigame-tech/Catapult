<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ApiRouteGenerator extends RouteGenerator
{
    protected function convertActionName(Entity $entity)
    {
        $converted = [];
        $plural = $this->inflector->pluralize($entity->name);
        foreach (ApiControllerGenerator::$apiActions as $actionName => $action) {
            $entityPath = $entity->name;
            $actionPath = '/' . $actionName;
            if ($actionName === 'index') {
                $entityPath = $plural;
                $actionPath = '';
            }

            $params = implode('/', array_map(
                fn ($p) => '{' . $p . '}',
                $action['params'])
            );
            $params = $params ? '/' . $params : '';

            $methods = is_array($action['method']) ? $action['method'] : [$action['method']];
            foreach ($methods as $method) {
                $converted[$actionName . '_' . $method] = "Route::{$method}('{$entityPath}{$actionPath}{$params}', "
                    . "[{$entity->apiControllerName()}::class, '{$actionName}'])->name('{$entity->name}.{$actionName}');";
            }
        }

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
