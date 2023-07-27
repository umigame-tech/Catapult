<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\DataList;
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

    protected function convertActionName(Entity $entity)
    {
        $converted = [];
        $plural = $this->inflector->pluralize($entity->name);
        $actions = ControllerGenerator::$actions;
        $controllerName= $entity->controllerName();
        foreach ($actions as $actionName => $action) {
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
                    . "[{$controllerName}::class, '{$actionName}'])->name('{$entity->name}.{$actionName}');";
            }
        }

        if ($entity->isAuthenticatable()) {
            $converted['dashboard'] = "Route::get('dashboard', "
                . "[{$controllerName}::class, 'dashboard'])->name('dashboard');";
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

    protected function convertEntitiesForRoute(DataList $entities)
    {
        $entities = $entities->map(
            function (Entity $entity) {
                $routes = $this->convertActionName($entity);
                $authName = $this->inflector->pluralize($entity->name);
                $loginRoutes = [];
                if ($entity->isAuthenticatable()) {
                    $controllerName = $entity->controllerName();
                    $loginRoutes['login'] = "Route::get('{$authName}/login', [{$controllerName}::class, 'login'])->name('{$authName}.login');";
                    $loginRoutes['loginSubmit'] = "Route::post('{$authName}/login', [{$controllerName}::class, 'loginSubmit'])->name('{$authName}.loginSubmit');";
                    $loginRoutes['logout'] = "Route::get('{$authName}/logout', [{$controllerName}::class, 'logout'])->name('{$authName}.logout');";
                }

                $entity->routes = $routes;
                $entity->loginRoutes = $loginRoutes;
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
