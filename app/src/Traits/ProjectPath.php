<?php

namespace UmigameTech\Catapult\Traits;

/**
 * @property string $targetDir
 * @property string $projectName
 */
trait ProjectPath
{
    protected function projectPath(): string
    {
        return "{$this->targetDir}/{$this->projectName}";
    }
}
