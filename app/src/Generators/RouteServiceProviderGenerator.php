<?php

namespace UmigameTech\Catapult\Generators;

class RouteServiceProviderGenerator extends Generator
{
    public function generate()
    {
        $source = __DIR__ . '/../Templates/Application/app/Providers/RouteServiceProvider.php';
        $dest = $this->projectPath() . '/app/Providers/RouteServiceProvider.php';

        $this->copier->copyFile($source, $dest);
    }
}
