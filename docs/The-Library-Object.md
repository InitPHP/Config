# The Library Object

`InitPHP\Config\Library` is the package's main entry point: an injectable
object that holds a configuration tree and knows how to populate it from
several sources. Inject it where you need isolated, testable
configuration — as opposed to the global [`Config` facade](The-Config-Facade.md).

```php
use InitPHP\Config\Library;

$config = new Library();                 // empty
$config = new Library(['db' => []]);     // seeded from an array
```

## The read/write surface

`Library` implements [`ConfigInterface`](API-Reference.md#configinterface):

```php
$config->set('app.name', 'InitPHP'); // returns $this — chainable
$config->get('app.name');            // 'InitPHP'
$config->get('app.locale', 'en');    // 'en' (default — key absent)
$config->has('app.name');            // true
$config->remove('app.name');         // returns $this
$config->all();                      // the whole tree as an array
```

See [Keys & Dotted Paths](Keys-and-Dotted-Paths.md) for how keys are parsed.

## Loaders

`Library` can import configuration from arrays, PHP files, directories,
and class/object properties. Each loader returns `$this`, so they chain
with everything else:

```php
$config
    ->setArray('site', ['url' => 'http://lvh.me'])
    ->setFile('db', __DIR__ . '/config/db.php')
    ->setDir('app', __DIR__ . '/config')
    ->setClass(\App\Config\Database::class);
```

Each one is documented in detail on the [Loaders](Loaders.md) page:

| Method | Loads from |
| --- | --- |
| [`setArray()`](Loaders.md#setarray) | An associative array. |
| [`setFile()`](Loaders.md#setfile) | A PHP file that returns an array. |
| [`setDir()`](Loaders.md#setdir) | Every `*.php` file in a directory. |
| [`setClass()`](Loaders.md#setclass) | A class's / object's public properties. |

## Replacing the whole tree

`set()` writes a single key; `replace()` discards the whole tree and
installs a new one:

```php
$config->set('old', 'value');
$config->replace(['fresh' => 'data']);

$config->has('old');   // false
$config->get('fresh'); // 'data'
```

## Object-style access

Top-level entries can be read as nested, read-only `stdClass` objects:

```php
$config->set('db.host', 'localhost')->set('db.user', 'root');

$config->db->host; // 'localhost'
$config->db->user; // 'root'
$config->name;     // a scalar entry is returned as-is
$config->unknown;  // null for an unknown key
```

This is covered on the [Object Access](Object-Access.md) page.

## Lifecycle

```php
$config->version(); // the package version, e.g. '2.0.0'
$config->close();   // clear the tree (the instance stays usable)
```

`close()` empties the configuration but keeps the instance ready for
reuse — the dotted-path and case-insensitive behaviour is preserved:

```php
$config->set('a.b', 'c');
$config->close();

$config->all();           // []
$config->set('x.y', 'z'); // still works
$config->get('x.y');      // 'z'
```

The object's destructor calls `close()` automatically, so there is
normally nothing to clean up by hand.

## Dependency injection

Because `Library` is a plain object, it slots into any container. Prefer
type-hinting against the interface so consumers can be tested with a
stub:

```php
use InitPHP\Config\Interfaces\ConfigInterface;
use InitPHP\Config\Library;

$container->set(ConfigInterface::class, function () {
    $config = new Library();
    $config->setDir('app', __DIR__ . '/config');

    return $config;
});

final class ReportService
{
    public function __construct(private ConfigInterface $config) {}

    public function timezone(): string
    {
        return $this->config->get('app.timezone', 'UTC');
    }
}
```

> **Heads-up:** `ConfigInterface` covers `get`/`set`/`has`/`remove`/`all`.
> If a consumer needs the loaders (`setFile`, `setDir`, …) or object
> access, type-hint against `Library` instead.

## Related reading

- [Loaders](Loaders.md) — every loader, with examples and error cases.
- [Object Access](Object-Access.md) — reading subtrees as objects.
- [The Config Facade](The-Config-Facade.md) — the static convenience layer.
- [Exceptions](Exceptions.md) — what loaders throw on failure.
