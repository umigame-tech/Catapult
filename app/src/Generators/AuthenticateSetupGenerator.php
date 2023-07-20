<?php

namespace UmigameTech\Catapult\Generators;

class AuthenticateSetupGenerator extends Generator
{
    public function generate()
    {
        $projectPath = $this->projectPath();

        $source =  __DIR__ . '/../Templates/app/Http/Middleware/Authenticate.php';
        $dist = $projectPath . '/app/Http/Middleware/Authenticate.php';

        $this->copier->copyFile($source, $dist);
    }
}
