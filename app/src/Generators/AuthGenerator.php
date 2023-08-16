<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class AuthGenerator extends Generator
{
    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $authPhpPath = $this->projectPath() . '/config/auth.php';

        $guards = $this->guards();
        $providers = $this->providers();
        $passwords = $this->passwords();

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

    public function generateSanctumContent()
    {
        $sanctumPhpPath = $this->projectPath() . '/config/sanctum.php';

        $guards = $this->guardNames();
        $renderer = Renderer::getInstance();
        $sanctumPhp = $renderer->render('sanctum.php.twig', [
            'guards' => $guards,
        ]);

        return [
            'path' => $sanctumPhpPath,
            'content' => $sanctumPhp,
        ];
    }

    public function generate()
    {
        $content = $this->generateContent();
        $this->writer->write(...$content);

        $sanctum = $this->generateSanctumContent();
        $this->writer->write(...$sanctum);
    }

    private function authenticatableList()
    {
        static $authenticatableList;
        if (!empty($authenticatableList)) {
            return $authenticatableList;
        }

        $authenticatableList = $this->entities->filter(
            fn (Entity $entity) => $entity->isAuthenticatable()
        );

        return $authenticatableList;
    }

    private function guardNames()
    {
        return $this->authenticatableList()->map(
            fn (Entity $entity) => $entity->authName(),
        );
    }

    private function guards()
    {
        return $this->authenticatableList()->map(
            fn (Entity $entity) => "'{$entity->authName()}' => [
            'driver' => 'session',
            'provider' => '{$entity->authName()}',
        ]",
        );
    }

    private function providers()
    {
        return $this->authenticatableList()->map(
            function (Entity $entity) {
                return "'{$entity->authName()}' => [
            'driver' => 'eloquent',
            'model' => App\\Models\\{$entity->modelName()}::class,
        ]";
            },
        );
    }

    private function passwords()
    {
        return $this->authenticatableList()->map(
            fn (Entity $entity) => "'{$entity->authName()}' => [
            'provider' => '{$entity->authName()}',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ]",
        );
    }
}
