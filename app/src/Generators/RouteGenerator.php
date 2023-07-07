<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class RouteGenerator extends Generator
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    private function convertActionName($entity, $prefix)
    {
        $inflector = InflectorFactory::create()->build();
        $converted = [];
        $actions = ControllerGenerator::$actions;
        foreach ($actions as $actionName => $action) {
            $spaces = $prefix ? '    ' : '';

            $entityName = $entity['name'];
            $actionPath = '/' . $actionName;
            if ($actionName === 'index') {
                $entityName = $inflector->pluralize($entity['name']);
                $actionPath = '';
            }

            $params = implode('/', array_map(
                fn ($p) => '{' . $p . '}',
                $action['params'])
            );
            $params = $params ? '/' . $params : '';

            $converted[$actionName] = "{$spaces}Route::{$action['method']}('{$entityName}{$actionPath}{$params}', "
                . "[{$entity['controllerName']}::class, '{$actionName}'])->name('{$entityName}.{$actionName}');";
        }

        return $converted;
    }

    public function generate($json)
    {
        $renderer = Renderer::getInstance();
        $webRoutePath = $this->projectPath() . '/routes/web.php';

        $prefix = $json['sealed_prefix'] ?? '';
        $entities = array_map(
            function ($entity) use ($prefix) {
                $entity['controllerName'] = ControllerGenerator::controllerName($entity);
                $entity['routes'] = $this->convertActionName($entity, $prefix);
                return $entity;
            },
            $json['entities']
        );

        $routes = $renderer->render('routes/web.php.twig', [
            'prefix' => $json['sealed_prefix'] ?? '',
            'entities' => $entities,
        ]);

        file_put_contents($webRoutePath, $routes);
    }
}
