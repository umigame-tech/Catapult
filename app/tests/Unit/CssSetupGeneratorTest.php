<?php

use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\CssSetupGenerator;

beforeEach(function () {
    $this->reader = new class implements FileReaderInterface {
        public function read($path)
        {
            return "";
        }
    };

    $this->writer = new class implements FileWriterInterface {
        public function write($path, $content): bool|int
        {
            return mb_strlen($content, '8bit');
        }
    };

    $this->removed = [];
    $outer = $this;
    $this->remover = new class($outer) implements FileRemoverInterface {
        public $outer;
        public function __construct($outer) {
            $this->outer = $outer;
        }
        public function remove($path): bool
        {
            $this->outer->removed[] = $path;
            return true;
        }
    };
});

test('generate', function () {

    $generator = new CssSetupGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
            'entities' => [],
        ],
        $this->reader,
        $this->writer,
        $this->remover
    );

    $result = $generator->generateContent();
    list('path' => $path, 'content' => $content) = $result;
    expect($content)
        ->toBeString()
        ->toContain('Sakura.css v');
});
