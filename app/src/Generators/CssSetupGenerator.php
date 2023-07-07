<?php

namespace UmigameTech\Catapult\Generators;

class CssSetupGenerator extends Generator
{
    public function generate()
    {
        $current = getcwd();
        chdir("{$this->projectPath()}/public");
        exec('wget "https://raw.githubusercontent.com/oxalorg/sakura/master/css/sakura.css"');
        chdir($current);
    }
}
