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
    $this->entity = [
        'name' => 'person',
        'authenticatable' => true,
    ];
    $outer = $this;
    $generator = new class($outer) extends Generator {
        public function __construct($outer)
        {
            return parent::__construct(
                [
                    'entities' => [ $outer->entity ],
                ],
                $outer->mocked
            );
        }
        public function getBaseUri($entity)
        {
            return $this->baseUri($entity);
        }
    };

    expect($generator->getBaseUri($this->entity))->toBe('/people/person');
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
