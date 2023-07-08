<?php

test('projectPath', function () {
    $class = new class {
        use \UmigameTech\Catapult\Traits\ProjectPath;

        public $targetDir = '/tmp';
        public $projectName = 'test';

        public function getProjectPath()
        {
            return $this->projectPath();
        }
    };

    expect($class->getProjectPath())->toBe('/tmp/test');
});
