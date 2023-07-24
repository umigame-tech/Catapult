<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class AuthGenerator extends Generator
{
    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $authPhpPath = $this->projectPath() . '/config/auth.php';

        $authenticatableList = $this->entities->filter(
            fn (Entity $entity) => $entity->isAuthenticatable()
        );

        $inflector = InflectorFactory::create()->build();

        $guards = $authenticatableList->map(
            fn (Entity $entity) => "'{$entity->authName()}' => [
            'driver' => 'session',
            'provider' => '{$entity->authName()}',
        ]",
        );

        $providers = $authenticatableList->map(
            function (Entity $entity) {
                return "'{$entity->authName()}' => [
            'driver' => 'eloquent',
            'model' => App\\Models\\{$entity->modelName()}::class,
        ]";
            },
        );

        $passwords = $authenticatableList->map(
            fn (Entity $entity) => "'{$entity->authName()}' => [
            'provider' => '{$entity->authName()}',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]",
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
