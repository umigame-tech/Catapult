<?php

namespace UmigameTech\Catapult\FileSystem;

/**
 * ファイル削除を分離することで
 * ロジックとファイルシステムの依存を切り離す
 */
class FileRemover implements FileRemoverInterface
{
    public function remove($path): bool
    {
        return unlink($path);
    }
}
