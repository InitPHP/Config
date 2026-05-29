<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests;

use InitPHP\Config\Config;
use InitPHP\Config\Library;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ConfigFacadeTest extends TestCase
{
    protected function setUp(): void
    {
        Config::reset();
    }

    protected function tearDown(): void
    {
        Config::reset();
    }

    public function testStaticDelegationStoresAndReads(): void
    {
        Config::setArray('site', ['url' => 'http://lvh.me']);

        $this->assertSame('http://lvh.me', Config::get('site.url'));
        $this->assertTrue(Config::has('site.url'));
    }

    public function testStateIsSharedAcrossCalls(): void
    {
        Config::set('app.name', 'InitPHP');

        $this->assertSame('InitPHP', Config::get('app.name'));
    }

    public function testFluentMethodsReturnLibrary(): void
    {
        $result = Config::set('a', 1);

        $this->assertInstanceOf(Library::class, $result);
        $this->assertSame(1, $result->get('a'));
    }

    public function testVersionIsExposed(): void
    {
        $this->assertSame(Library::VERSION, Config::version());
    }

    public function testResetClearsSharedState(): void
    {
        Config::set('temp', 'value');
        Config::reset();

        $this->assertSame('default', Config::get('temp', 'default'));
    }

    public function testResetWhenNoInstanceIsSafe(): void
    {
        Config::reset();
        Config::reset();

        $this->assertNull(Config::get('anything'));
    }

    public function testFacadeCannotBeInstantiated(): void
    {
        $constructor = (new ReflectionClass(Config::class))->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPrivate());
    }
}
