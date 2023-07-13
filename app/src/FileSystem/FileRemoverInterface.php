<?php

namespace UmigameTech\Catapult\FileSystem;

interface FileRemoverInterface
{
    public function remove($path): bool;
}
