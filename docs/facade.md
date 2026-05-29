# The Static Facade

[`InitPHP\Config\Config`](../src/Config.php) is a static facade over a
single, lazily created, process-wide [`Library`](library.md) instance. It
lets you register configuration once and read it from anywhere without
passing an object around.

```php
use InitPHP\Config\Config;

Config::setArray('site', ['url' => 'http://lvh.me']);

// ...elsewhere in the request, no wiring required:
Config::get('site.url'); // "http://lvh.me"
```

## Available calls

Every `Library` method is reachable statically and operates on the shared
instance:

```php
Config::set('app.name', 'InitPHP');
Config::get('app.name');                 // "InitPHP"
Config::has('app.name');                 // true
Config::remove('app.name');
Config::all();

Config::setArray('db', ['host' => 'localhost']);
Config::setFile('cache', __DIR__ . '/config/cache.php');
Config::setDir('app', __DIR__ . '/config');
Config::setClass(\App\Config\Database::class);
Config::replace(['fresh' => 'tree']);

Config::version(); // "2.0.0"
```

## Fluent calls return the Library

Methods that would return `$this` on a `Library` return the underlying
instance from the facade, so you can keep chaining:

```php
use InitPHP\Config\Library;

$library = Config::set('a', 1)->set('b', 2);

$library instanceof Library; // true
$library->get('a');          // 1
```

## Resetting between runs

Because the instance is process-wide, long-running workers and especially
**tests** need a way to start clean. `Config::reset()` discards the shared
instance; the next call creates a fresh, empty one.

```php
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        Config::reset();
    }

    public function testSomething(): void
    {
        Config::set('flag', true);
        $this->assertTrue(Config::get('flag'));
    }
}
```

## Facade or object?

| Use the facade when… | Use a `Library` when… |
| --- | --- |
| You want zero wiring and global reach. | You practice dependency injection. |
| Configuration is truly process-global. | You need isolated instances (e.g. multi-tenant). |
| Convenience outweighs testability concerns. | You want easy, reset-free test isolation. |

The facade is a convenience layer — under the hood it is the exact same
[`Library`](library.md), so behaviour (dotted paths, case-insensitivity,
exceptions) is identical.
