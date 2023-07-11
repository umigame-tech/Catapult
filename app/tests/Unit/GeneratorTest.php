<?php

use UmigameTech\Catapult\Generators\Generator;

test('indents', function () {
    $generator = new class extends Generator {
        public function __construct()
        {
            return parent::__construct([]);
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
            return parent::__construct([
                'sealed_prefix' => 'admin',
            ]);
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
            return parent::__construct([]);
        }
        public function getBaseUri($entity)
        {
            return $this->baseUri($entity);
        }
    };

    expect($generator->getBaseUri(['name' => 'user']))->toBe('/user');
});
