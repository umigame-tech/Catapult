<?php

namespace UmigameTech\Catapult\Generators;

class ResourcesSetupGenerator extends Generator
{
    public function generate()
    {
        $projectPath = $this->projectPath();

        // src/templates/resources/ 配下のディレクトリをコピーする
        $resourcesDir = __DIR__ . '/../templates/resources';
        $distDir = $projectPath . '/resources';
        $this->copyDir($resourcesDir, $distDir);
    }

    private function copyDir($source, $dest)
    {
        if (!file_exists($dest)) {
            mkdir($dest);
        }

        foreach (scandir($source) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = "{$source}/{$file}";
            $destPath = "{$dest}/{$file}";
            if (is_dir($sourcePath)) {
                $this->copyDir($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
    }
}
