<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class RouteGenerator extends Generator
{
    const FOR_EVERYONE = ['all', 'everyone', 'any', '*'];

    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    private function convertActionName($entity)
    {
        $converted = [];
        $plural = $this->inflector->pluralize($entity['name']);
        $actions = ControllerGenerator::$actions;
        foreach ($actions as $actionName => $action) {
            $entityPath = $entity['name'];
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

            $converted[$actionName] = "Route::{$action['method']}('{$entityPath}{$actionPath}{$params}', "
                . "[{$entity['controllerName']}::class, '{$actionName}'])->name('{$entity['name']}.{$actionName}');";
        }

        if ($entity['authenticatable'] ?? false) {
            $converted['dashboard'] = "Route::get('dashboard', "
                . "[{$entity['controllerName']}::class, 'dashboard'])->name('dashboard');";
            $converted['home'] = "Route::get('/', fn () => redirect()->route('{$plural}.dashboard'));";
        }

        return $converted;
    }

    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $webRoutePath = $this->projectPath() . '/routes/web.php';

        $authList = $this->makeAuthList();

        $forEveryone = $this->routesForEveryone();

        $routes = $renderer->render('routes/web.php.twig', [
            'authList' => $authList,
            'forEveryone' => $forEveryone,
            'entities' => $this->entities,
        ]);

        return [
            'path' => $webRoutePath,
            'content' => $routes,
        ];
    }

    private function makeAuthList()
    {
        $authNames = array_values(array_map(
            fn ($entity) => $this->inflector->pluralize($entity['name']),
            array_filter(
                $this->entities,
                fn ($entity) => $entity['authenticatable'] ?? false
            )
        ));
        $authList = [];
        foreach ($authNames as $authName) {
            $filtered = array_filter(
                $this->entities,
                function ($entity) use ($authName) {
                    $allowedFor = array_map(
                        fn ($allowed) => $this->inflector->pluralize($allowed),
                        $entity['allowedFor'] ?? []
                    );
                    return in_array($authName, $allowedFor);
                }
            );

            $authList[$authName] = $this->convertEntitiesForRoute($filtered, 1);
        }

        return $authList;
    }

    private function routesForEveryone()
    {
        $filtered = array_filter(
            $this->entities,
            function ($entity) {
                foreach ($entity['allowedFor'] ?? [] as $allowed) {
                    if (in_array($allowed, self::FOR_EVERYONE)) {
                        return true;
                    }
                }

                return false;
            }
        );

        return $this->convertEntitiesForRoute($filtered);
    }

    private function convertEntitiesForRoute($entities)
    {
        $entities = array_map(
            function ($entity) {
                $entity['controllerName'] = ControllerGenerator::controllerName($entity);
                $routes = $this->convertActionName($entity);
                $authName = $this->inflector->pluralize($entity['name']);
                $loginRoutes = [];
                if ($entity['authenticatable'] ?? false) {
                    $controllerName = $entity['controllerName'];
                    $loginRoutes['login'] = "Route::get('{$authName}/login', [{$controllerName}::class, 'login'])->name('{$authName}.login');";
                    $loginRoutes['loginSubmit'] = "Route::post('{$authName}/login', [{$controllerName}::class, 'loginSubmit'])->name('{$authName}.loginSubmit');";
                    $loginRoutes['logout'] = "Route::get('{$authName}/logout', [{$controllerName}::class, 'logout'])->name('{$authName}.logout');";
                }

                $entity['routes'] = $routes;
                $entity['loginRoutes'] = $loginRoutes;
                return $entity;
            },
            $entities
        );

        return $entities;
    }

    public function generate()
    {
        $content = $this->generateContent();
        $this->writer->write(...$content);
    }
}
