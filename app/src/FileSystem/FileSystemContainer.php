<?php

namespace UmigameTech\Catapult\FileSystem;

use UmigameTech\Catapult\FileSystem\FileReader;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemover;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileWriter;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;

class FileSystemContainer
{
    public FileReaderInterface $reader;
    public FileWriterInterface $writer;
    public FileRemoverInterface $remover;
    public FileCheckerInterface $checker;
    public CopyFileInterface $copier;
    public MakeDirectoryInterface $makeDirectory;

    public function __construct(
        FileReaderInterface $reader = null,
        FileWriterInterface $writer = null,
        FileRemoverInterface $remover = null,
        FileCheckerInterface $checker = null,
        CopyFileInterface $copier = null,
        MakeDirectoryInterface $makeDirectory = null,
    ) {
        if (empty($reader)) {
            $reader = new FileReader;
        }

        if (empty($writer)) {
            $writer = new FileWriter;
        }

        if (empty($remover)) {
            $remover = new FileRemover;
        }

        if (empty($checker)) {
            $checker = new FileChecker;
        }

        if (empty($copier)) {
            $copier = new CopyFile;
        }

        if (empty($makeDirectory)) {
            $makeDirectory = new MakeDirectory;
        }

        $this->reader = $reader;
        $this->writer = $writer;
        $this->remover = $remover;
        $this->checker = $checker;
        $this->copier = $copier;
        $this->makeDirectory = $makeDirectory;
    }
}
