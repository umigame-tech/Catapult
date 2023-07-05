<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Templates\Renderer;

class RouteGenerator extends Generator
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    public function generate($json)
    {
        $renderer = Renderer::getInstance();
        $webRoutePath = $this->projectPath() . '/routes/web.php';

        $entities = array_map(
            function ($entity) {
                $entity['controllerName'] = ControllerGenerator::controllerName($entity);
                return $entity;
            },
            $json['entities']
        );

        $routes = $renderer->render('routes/web.php.twig', [
            'prefix' => $json['sealed_prefix'] ?? '',
            'entities' => $entities,
            'actions' => ControllerGenerator::$actions,
        ]);

        file_put_contents($webRoutePath, $routes);
    }
}
