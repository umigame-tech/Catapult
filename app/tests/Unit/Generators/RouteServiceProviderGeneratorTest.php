<?php

use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\RouteServiceProviderGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $generator = new RouteServiceProviderGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [],
        ]),
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->copied)->toHaveLength(1);
    expect($this->mocked->copied[0]['source'])
        ->toContain('RouteServiceProvider.php')
        ->toContain('Templates');
    expect($this->mocked->copied[0]['dest'])
        ->toContain('RouteServiceProvider.php')
        ->toContain('test');
});
