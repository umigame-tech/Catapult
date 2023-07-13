<?php

namespace UmigameTech\Catapult\FileSystem;

/**
 * ファイル書き込みを分離することで
 * ロジックとファイル書き込みを分離する
 */
class FileWriter implements FileWriterInterface
{
    public function __construct()
    {
    }

    public function write($path, $content): bool|int
    {
        $dir = dirname($path);

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        return file_put_contents($path, $content);
    }
}
