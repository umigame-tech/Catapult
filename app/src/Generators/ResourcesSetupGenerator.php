<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\FileSystem\CopyDirectory;
use UmigameTech\Catapult\FileSystem\CopyDirectoryInterface;

class ResourcesSetupGenerator extends Generator
{
    public function generate(CopyDirectoryInterface $copyDirectory = new CopyDirectory)
    {
        $projectPath = $this->projectPath();

        // src/Templates/Application/resources/ 配下のディレクトリをコピーする
        $resourcesDir = __DIR__ . '/../Templates/Application/resources';
        $distDir = $projectPath . '/resources';

        $copyDirectory->copyDir($resourcesDir, $distDir);
    }
}
