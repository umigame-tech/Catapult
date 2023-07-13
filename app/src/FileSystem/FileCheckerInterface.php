<?php

namespace UmigameTech\Catapult\FileSystem;

interface FileCheckerInterface
{
    public function exists($path): bool;
}
