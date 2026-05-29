<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests;

use InitPHP\Config\Exceptions\ConfigException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionTest extends TestCase
{
    public function testIsRuntimeException(): void
    {
        $this->assertInstanceOf(RuntimeException::class, ConfigException::fileNotFound('x'));
    }

    public function testFileNotFoundMessage(): void
    {
        $this->assertSame(
            'Configuration file "/a/b.php" was not found.',
            ConfigException::fileNotFound('/a/b.php')->getMessage()
        );
    }

    public function testFileMustReturnArrayMessage(): void
    {
        $this->assertSame(
            'Configuration file "/a/b.php" must return an array.',
            ConfigException::fileMustReturnArray('/a/b.php')->getMessage()
        );
    }

    public function testNotAPhpFileMessage(): void
    {
        $this->assertSame(
            'Configuration file "/a/b.txt" must have a ".php" extension.',
            ConfigException::notAPhpFile('/a/b.txt')->getMessage()
        );
    }

    public function testNotADirectoryMessage(): void
    {
        $this->assertSame(
            '"/a/b" is not a valid directory.',
            ConfigException::notADirectory('/a/b')->getMessage()
        );
    }

    public function testDirectoryNotReadableMessage(): void
    {
        $this->assertSame(
            'Could not read the directory "/a/b".',
            ConfigException::directoryNotReadable('/a/b')->getMessage()
        );
    }

    public function testClassNotFoundMessage(): void
    {
        $this->assertSame(
            'Class "App\\Foo" was not found.',
            ConfigException::classNotFound('App\\Foo')->getMessage()
        );
    }
}
