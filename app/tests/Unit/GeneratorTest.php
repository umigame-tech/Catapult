<?php

use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\Generator;

beforeEach(function () {
    $this->reader = new class implements FileReaderInterface {
        public function read($path)
        {
            return "";
        }
    };

    $this->contents = [];

    $outer = $this;
    $this->writer = new class($outer) implements FileWriterInterface {
        public $outer;
        public function __construct($outer) {
            $this->outer = $outer;
        }
        public function write($path, $content): bool|int
        {
            $this->outer->contents[] = $content;
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
test('indents', function () {
    $generator = new class extends Generator {
        public function __construct()
        {
            return parent::__construct(
                [],
                $this->reader,
                $this->writer,
                $this->remover,
            );
        }
        public function getIndents($level)
        {
            return $this->indents($level);
        }
    };

    expect($generator->getIndents(0))->toBe('');
    expect($generator->getIndents(1))->toBe('    ');
    expect($generator->getIndents(10))->toBe(str_repeat('    ', 10));
});

test('baseUri with prefix', function () {
    $generator = new class extends Generator {
        public function __construct()
        {
            return parent::__construct(
                [
                    'sealed_prefix' => 'admin',
                ],
                $this->reader,
                $this->writer,
                $this->remover
            );
        }
        public function getBaseUri($entity)
        {
            return $this->baseUri($entity);
        }
    };

    expect($generator->getBaseUri(['name' => 'user']))->toBe('/admin/user');
});

test('baseUri without prefix', function () {
    $generator = new class extends Generator {
        public function __construct()
        {
            return parent::__construct(
                [],
                $this->reader,
                $this->writer,
                $this->remover
            );
        }
        public function getBaseUri($entity)
        {
            return $this->baseUri($entity);
        }
    };

    expect($generator->getBaseUri(['name' => 'user']))->toBe('/user');
});
