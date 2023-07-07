<?php

namespace UmigameTech\Catapult\FileSystem;

class RemoveDirectory
{
    public function remove($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $this->removeDir($dir);
    }

    private function removeDir($dir)
    {
        // RecursiveDirectoryIteratorを使ってディレクトリを再帰的に削除する
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }
}
