<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class AuthGenerator extends Generator
{
    private static $authName;
    private static $inflector;

    public static function authName($entity)
    {
        if (!empty(self::$authName)) {
            return self::$authName;
        }

        if (empty(self::$inflector)) {
            self::$inflector = InflectorFactory::create()->build();
        }

        self::$authName = self::$inflector->pluralize($entity['name']);
        return self::$authName;
    }

    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $authPhpPath = $this->projectPath() . '/config/auth.php';

        $authenticatableList = array_values(array_filter(
            $this->entities,
            fn ($entity) => $entity['authenticatable'] ?? false
        ));

        $inflector = InflectorFactory::create()->build();
        $authenticatableList = array_map(
            function ($entity) use ($inflector) {
                $entity['plural'] = $inflector->pluralize($entity['name']);
                return $entity;
            },
            $authenticatableList
        );

        $guards = array_map(
            fn ($entity) => "'{$entity['plural']}' => [
            'driver' => 'session',
            'provider' => '{$entity['plural']}',
        ]",
            $authenticatableList
        );

        $providers = array_map(
            function ($entity) {
                $modelName = ModelGenerator::modelName($entity);
                return "'{$entity['plural']}' => [
            'driver' => 'eloquent',
            'model' => App\\Models\\{$modelName}::class,
        ]";
            },
            $authenticatableList
        );

        $passwords = array_map(
            fn ($entity) => "'{$entity['plural']}' => [
            'provider' => '{$entity['plural']}',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]",
            $authenticatableList
        );

        $authPhp = $renderer->render('auth.php.twig', [
            'providers' => $providers,
            'guards' => $guards,
            'passwords' => $passwords,
        ]);

        return [
            'path' => $authPhpPath,
            'content' => $authPhp,
        ];
    }

    public function generate()
    {
        $content = $this->generateContent();
        $this->writer->write(...$content);
    }
}
