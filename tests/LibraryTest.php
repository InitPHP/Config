<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests;

use InitPHP\Config\Interfaces\ConfigInterface;
use InitPHP\Config\Library;
use PHPUnit\Framework\TestCase;
use stdClass;

final class LibraryTest extends TestCase
{
    private Library $library;

    protected function setUp(): void
    {
        $this->library = new Library();
    }

    public function testImplementsConfigInterface(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->library);
    }

    public function testConstructorSeedsTree(): void
    {
        $library = new Library(['url' => 'http://lvh.me']);

        $this->assertSame('http://lvh.me', $library->get('url'));
    }

    public function testVersionMatchesConstant(): void
    {
        $this->assertSame(Library::VERSION, $this->library->version());
    }

    public function testSetGetScalar(): void
    {
        $this->assertSame($this->library, $this->library->set('name', 'InitPHP'));
        $this->assertSame('InitPHP', $this->library->get('name'));
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        $this->assertNull($this->library->get('missing'));
        $this->assertSame('fallback', $this->library->get('missing', 'fallback'));
    }

    public function testDottedPathSetAndGet(): void
    {
        $this->library->set('db.host', 'localhost');

        $this->assertSame('localhost', $this->library->get('db.host'));
        $this->assertSame(['host' => 'localhost'], $this->library->get('db'));
    }

    public function testKeysAreCaseInsensitive(): void
    {
        $this->library->set('DB.Host', 'localhost');

        $this->assertSame('localhost', $this->library->get('db.host'));
        $this->assertSame('localhost', $this->library->get('DB.HOST'));
        $this->assertTrue($this->library->has('Db.HoSt'));
    }

    public function testHasDistinguishesNullValueFromMissing(): void
    {
        $this->library->set('nullable', null);

        $this->assertTrue($this->library->has('nullable'));
        $this->assertFalse($this->library->has('absent'));
    }

    public function testRemove(): void
    {
        $this->library->set('a.b', 'c');
        $this->assertSame($this->library, $this->library->remove('a.b'));
        $this->assertFalse($this->library->has('a.b'));
        $this->assertSame('gone', $this->library->get('a.b', 'gone'));
    }

    public function testRemoveMissingKeyIsNoop(): void
    {
        $this->library->remove('neverset');

        $this->assertSame([], $this->library->all());
    }

    public function testAllReturnsWholeTree(): void
    {
        $this->library->set('a', 1)->set('b.c', 2);

        $this->assertSame(['a' => 1, 'b' => ['c' => 2]], $this->library->all());
    }

    public function testReplaceDiscardsExistingEntries(): void
    {
        $this->library->set('old', 'value');
        $this->assertSame($this->library, $this->library->replace(['fresh' => 'data']));

        $this->assertFalse($this->library->has('old'));
        $this->assertSame('data', $this->library->get('fresh'));
    }

    public function testMagicGetReturnsNestedObject(): void
    {
        $this->library->set('db.host', 'localhost')->set('db.user', 'root');

        $db = $this->library->db;

        $this->assertInstanceOf(stdClass::class, $db);
        $this->assertSame('localhost', $db->host);
        $this->assertSame('root', $db->user);
    }

    public function testMagicGetDeepNesting(): void
    {
        $this->library->set('a.b.c', 'deep');

        $this->assertSame('deep', $this->library->a->b->c);
    }

    public function testMagicGetScalarReturnedAsIs(): void
    {
        $this->library->set('name', 'InitPHP');

        $this->assertSame('InitPHP', $this->library->name);
    }

    public function testMagicGetUnknownReturnsNull(): void
    {
        $this->assertNull($this->library->unknown);
    }

    public function testDebugInfoExposesVersionAndData(): void
    {
        $this->library->set('a', 1);
        $info = $this->library->__debugInfo();

        $this->assertSame(Library::VERSION, $info['version']);
        $this->assertSame(['a' => 1], $info['data']);
    }

    public function testCloseClearsButKeepsInstanceUsable(): void
    {
        $this->library->set('a.b', 'c');
        $this->library->close();

        $this->assertSame([], $this->library->all());

        // The dotted-path option must survive a close().
        $this->library->set('x.y', 'z');
        $this->assertSame('z', $this->library->get('x.y'));
    }
}
