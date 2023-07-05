<?php

namespace UmigameTech\Catapult\Templates;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Engine
{
    private static Engine $instance;

    private $twig;

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new Engine();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__);
        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);
    }

    public function render($template, $context)
    {
        return $this->twig->render($template, $context);
    }
}
