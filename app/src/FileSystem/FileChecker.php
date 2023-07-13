<?php

namespace UmigameTech\Catapult\FileSystem;

class FileChecker implements FileCheckerInterface
{
    public function exists($path): bool
    {
        return file_exists($path);
    }
}
