<?php

namespace UmigameTech\Catapult\Generators;

class CssSetupGenerator extends Generator
{
    public function generate()
    {
        $current = getcwd();
        chdir("{$this->projectPath()}/public");

        if (!file_exists('sakura.css')) {
            $sakura = file_get_contents("https://raw.githubusercontent.com/oxalorg/sakura/master/css/sakura.css");
            file_put_contents('sakura.css', $sakura);
            chdir($current);
            return;
        }

        // 1週間以上経過していたら更新する
        $oneWeek = 60 * 60 * 24 * 7;
        if (filemtime('sakura.css') < time() - $oneWeek) {
            unlink('sakura.css');
            $sakura = file_get_contents("https://raw.githubusercontent.com/oxalorg/sakura/master/css/sakura.css");
            file_put_contents('sakura.css', $sakura);
        }

        chdir($current);
    }
}
