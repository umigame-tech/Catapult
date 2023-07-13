<?php

use UmigameTech\Catapult\Generators\Generator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});
test('indents', function () {
    $outer = $this;
    $generator = new class($outer) extends Generator {
        public function __construct($outer)
        {
            return parent::__construct(
                [],
                $outer->mocked
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
    $outer = $this;
    $generator = new class($outer) extends Generator {
        public function __construct($outer)
        {
            return parent::__construct(
                [
                    'sealed_prefix' => 'admin',
                ],
                $outer->mocked
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
    $outer = $this;
    $generator = new class($outer) extends Generator {
        public function __construct($outer)
        {
            return parent::__construct(
                [],
                $outer->mocked
            );
        }
        public function getBaseUri($entity)
        {
            return $this->baseUri($entity);
        }
    };

    expect($generator->getBaseUri(['name' => 'user']))->toBe('/user');
});
