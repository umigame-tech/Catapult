<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

use UmigameTech\Catapult\FileSystem\CopyFileInterface;
use UmigameTech\Catapult\FileSystem\FileCheckerInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\FileSystem\FileSystemContainer;
use UmigameTech\Catapult\FileSystem\MakeDirectoryInterface;

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

class MockedFileSystem extends FileSystemContainer
{
    public array $contents = [];
    public array $removed = [];
    public array $copied = [];
}

function mockFileSystems()
{
    $mocked = new MockedFileSystem;
    $mocked->reader = new class implements FileReaderInterface {
        public function read($path)
        {
            return "";
        }
    };

    $outer = $mocked;
    $mocked->writer = new class($outer) implements FileWriterInterface {
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

    $outer = $mocked;
    $mocked->remover = new class($outer) implements FileRemoverInterface {
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

    $mocked->checker = new class implements FileCheckerInterface {
        public function exists($path): bool
        {
            return false;
        }
    };


    $mocked->copier = new class($outer) implements CopyFileInterface {
        public $outer;
        public function __construct($outer) {
            $this->outer = $outer;
        }

        public function copyFile($source, $dest)
        {
            $this->outer->copied[] = compact('source', 'dest');
        }
    };

    $mocked->makeDirectory = new class implements MakeDirectoryInterface {
        public function mkdir(string $path, int $mode = 0755, bool $recursive = false): bool
        {
            return true;
        }
    };

    return $mocked;
}
