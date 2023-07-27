<?php

namespace UmigameTech\Catapult\FileSystem;

interface MakeDirectoryInterface
{
    public function mkdir(string $path, int $mode = 0755, bool $recursive = false): bool;
}
