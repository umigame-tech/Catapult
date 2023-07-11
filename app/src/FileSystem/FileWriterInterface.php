<?php

namespace UmigameTech\Catapult\FileSystem;

interface FileWriterInterface
{
    public function write($path, $content): bool|int;
}
