<?php

namespace UmigameTech\Catapult\FileSystem;

class CopyFile implements CopyFileInterface
{
    public function copyFile($source, $dest)
    {
        return copy($source, $dest);
    }
}
