<?php

namespace UmigameTech\Catapult\FileSystem;

class MakeDirectory implements MakeDirectoryInterface
{
    public function mkdir(string $path, int $mode = 0755, bool $recursive = false): bool
    {
        return mkdir($path, $mode, $recursive);
    }
}
