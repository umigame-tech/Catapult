<?php

namespace UmigameTech\Catapult\FileSystem;

/**
 * ファイル読み込みを分離することで
 * ロジックとファイル読み込みを分離する
 */
class FileReader implements FileReaderInterface
{
    public function __construct()
    {
    }

    public function read($path)
    {
        return file_get_contents($path);
    }
}
