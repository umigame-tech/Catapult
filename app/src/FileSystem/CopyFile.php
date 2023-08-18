<?php

namespace UmigameTech\Catapult\FileSystem;

class CopyFile implements CopyFileInterface
{
    private FileCheckerInterface $checker;
    private MakeDirectoryInterface $makeDirectory;
    public function __construct(
        FileCheckerInterface $checker = new FileChecker,
        MakeDirectoryInterface $makeDirectory = new MakeDirectory
    )
    {
        $this->checker = $checker;
        $this->makeDirectory = $makeDirectory;
    }

    public function copyFile($source, $dest)
    {
        $destDir = dirname($dest);
        if (!$this->checker->exists($destDir)) {
            $this->makeDirectory->mkdir($destDir, 0755, true);
        }

        return copy($source, $dest);
    }
}
