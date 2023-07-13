<?php

namespace UmigameTech\Catapult\FileSystem;

class CopyDirectory implements CopyDirectoryInterface
{
    public function copyDir($source, $dest)
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
