<?php

namespace UmigameTech\Catapult\Generators;

class CssSetupGenerator extends Generator
{
    public function generateContent()
    {
        $cssPath = "{$this->projectPath()}/resources/css/sakura.css";
        if (!file_exists('sakura.css')) {
            $sakura = file_get_contents("https://raw.githubusercontent.com/oxalorg/sakura/master/css/sakura.css");
            return [
                'path' => $cssPath,
                'content' => $sakura,
            ];
        }

        // 1週間以上経過していたら更新する
        $oneWeek = 60 * 60 * 24 * 7;
        if (filemtime('sakura.css') < time() - $oneWeek) {
            unlink('sakura.css');
            $sakura = file_get_contents("https://raw.githubusercontent.com/oxalorg/sakura/master/css/sakura.css");
            return [
                'path' => $cssPath,
                'content' => $sakura,
            ];
        }

        // それ以外は何もしない
        return [];
    }

    public function generate()
    {
        $result = $this->generateContent();
        if (empty($result)) {
            return;
        }

        $this->writer->write(...$result);
    }
}
