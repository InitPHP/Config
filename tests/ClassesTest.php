<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests;

use InitPHP\Config\Interfaces\ConfigInterface;
use InitPHP\Config\Tests\Fixtures\SampleAppConfig;
use InitPHP\Config\Tests\Fixtures\VisibilityConfig;
use PHPUnit\Framework\TestCase;

final class ClassesTest extends TestCase
{
    private SampleAppConfig $config;

    protected function setUp(): void
    {
        $this->config = new SampleAppConfig();
    }

    public function testImplementsConfigInterface(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
    }

    public function testImportsPublicPropertyDefaults(): void
    {
        $this->assertSame('http://lvh.me', $this->config->get('url'));
        $this->assertSame('LocalHost', $this->config->get('name'));
    }

    public function testImportsNestedArrayProperty(): void
    {
        $this->assertSame('localhost', $this->config->get('db.host'));
        $this->assertSame('root', $this->config->get('db.user'));
    }

    public function testDoesNotLeakInfrastructureProperty(): void
    {
        $this->assertFalse($this->config->has('parameterBag'));
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        $this->assertSame('Not Found', $this->config->get('details', 'Not Found'));
    }

    public function testSetAndRemoveAtRuntime(): void
    {
        $this->config->set('db.pass', 'secret');
        $this->assertSame('secret', $this->config->get('db.pass'));

        $this->config->remove('db.pass');
        $this->assertFalse($this->config->has('db.pass'));
    }

    public function testHasIsCaseInsensitive(): void
    {
        $this->assertTrue($this->config->has('URL'));
        $this->assertTrue($this->config->has('DB.HOST'));
    }

    public function testImportsPublicAndProtectedButNotPrivate(): void
    {
        $config = new VisibilityConfig();

        $this->assertSame('public', $config->get('publicValue'));
        $this->assertSame('protected', $config->get('protectedValue'));
        $this->assertFalse($config->has('privateValue'));
    }

    public function testAllReturnsEveryEntry(): void
    {
        $all = $this->config->all();

        $this->assertArrayHasKey('url', $all);
        $this->assertArrayHasKey('db', $all);
        $this->assertSame('localhost', $all['db']['host']);
    }
}
