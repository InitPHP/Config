<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests;

use InitPHP\Config\Exceptions\ConfigException;
use InitPHP\Config\Library;
use InitPHP\Config\Tests\Fixtures\PlainSettings;
use PHPUnit\Framework\TestCase;

final class LoadersTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/Fixtures';

    private Library $library;

    protected function setUp(): void
    {
        $this->library = new Library();
    }

    public function testSetArrayUnderName(): void
    {
        $this->library->setArray('site', [
            'url' => 'http://lvh.me',
            'db'  => ['host' => 'localhost'],
        ]);

        $this->assertSame('http://lvh.me', $this->library->get('site.url'));
        $this->assertSame('localhost', $this->library->get('site.db.host'));
    }

    public function testSetArrayWithNullNameMergesAtRoot(): void
    {
        $this->library->set('existing', 'kept');
        $this->library->setArray(null, ['url' => 'http://lvh.me']);

        $this->assertSame('kept', $this->library->get('existing'));
        $this->assertSame('http://lvh.me', $this->library->get('url'));
    }

    public function testSetArrayWithEmptyNameMergesAtRoot(): void
    {
        $this->library->setArray('', ['url' => 'http://lvh.me']);

        $this->assertSame('http://lvh.me', $this->library->get('url'));
    }

    public function testSetFileUnderName(): void
    {
        $this->library->setFile('db', self::FIXTURES . '/config/db.php');

        $this->assertSame('localhost', $this->library->get('db.host'));
        $this->assertSame('root', $this->library->get('db.user'));
    }

    public function testSetFileWithNullNameMergesAtRoot(): void
    {
        $this->library->setFile(null, self::FIXTURES . '/config/db.php');

        $this->assertSame('localhost', $this->library->get('host'));
    }

    public function testSetFileThrowsWhenMissing(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('was not found');

        $this->library->setFile('db', self::FIXTURES . '/config/does-not-exist.php');
    }

    public function testSetFileThrowsWhenNotReturningArray(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('must return an array');

        $this->library->setFile('x', self::FIXTURES . '/scalar_return.php');
    }

    public function testSetFileThrowsWhenNotPhpExtension(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('.php');

        $this->library->setFile('x', self::FIXTURES . '/not_php.txt');
    }

    public function testSetDirAppliesPrefix(): void
    {
        $this->library->setDir('app', self::FIXTURES . '/config');

        $this->assertSame('localhost', $this->library->get('app.db.host'));
        $this->assertSame('http://lvh.me', $this->library->get('app.site.url'));
        $this->assertSame('file', $this->library->get('app.cache.driver'));
    }

    public function testSetDirWithoutNameHasNoPrefix(): void
    {
        $this->library->setDir(null, self::FIXTURES . '/config');

        $this->assertSame('localhost', $this->library->get('db.host'));
        $this->assertSame('http://lvh.me', $this->library->get('site.url'));
    }

    public function testSetDirExcludesNamedFiles(): void
    {
        $this->library->setDir('app', self::FIXTURES . '/config', ['db', 'cache.php']);

        $this->assertFalse($this->library->has('app.db'));
        $this->assertFalse($this->library->has('app.cache'));
        $this->assertTrue($this->library->has('app.site'));
    }

    public function testSetDirThrowsWhenNotADirectory(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('not a valid directory');

        $this->library->setDir('app', self::FIXTURES . '/config/db.php');
    }

    public function testSetClassFromClassNameUsesDefaults(): void
    {
        $this->library->setClass(PlainSettings::class);

        $this->assertSame('production', $this->library->get('plainsettings.env'));
        $this->assertSame(60, $this->library->get('plainsettings.limits.rate'));
    }

    public function testSetClassOnlyImportsPublicProperties(): void
    {
        $this->library->setClass(PlainSettings::class);

        $this->assertFalse($this->library->has('plainsettings.secret'));
    }

    public function testSetClassFromObjectUsesRuntimeValues(): void
    {
        $settings = new PlainSettings();
        $settings->env = 'staging';

        $this->library->setClass($settings);

        $this->assertSame('staging', $this->library->get('plainsettings.env'));
    }

    public function testSetClassThrowsWhenClassMissing(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('was not found');

        $this->library->setClass('No\\Such\\Class');
    }
}
