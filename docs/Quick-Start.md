# Quick Start

A five-minute tour of the three entry points. Every snippet runs as-is.

## 1. Configuration as a class

Declare configuration as public properties and read it back through the
shared API:

```php
use InitPHP\Config\Classes;

final class AppConfig extends Classes
{
    public string $url  = 'http://lvh.me';
    public string $name = 'LocalHost';

    /** @var array<string, string> */
    public array $db = [
        'host' => 'localhost',
        'user' => 'root',
    ];
}

$config = new AppConfig();

$config->get('url');                  // 'http://lvh.me'
$config->get('db.host');              // 'localhost'   (nested array → dotted path)
$config->get('details', 'Not Found'); // 'Not Found'   (default — key absent)
$config->has('name');                 // true
```

See [Configuration Classes](Configuration-Classes.md) for the full story.

## 2. The Library object

`Library` is an injectable object you fill at runtime. All write methods
return `$this`, so they chain:

```php
use InitPHP\Config\Library;

$config = new Library();

$config->set('site.url', 'http://lvh.me')
       ->set('site.db.host', 'localhost')
       ->set('site.db.user', 'root');

$config->get('site.url');     // 'http://lvh.me'
$config->get('site.db.host'); // 'localhost'
$config->get('SITE.DB.HOST'); // 'localhost'  (case-insensitive)
$config->all();               // ['site' => ['url' => ..., 'db' => [...]]]
```

You can also seed it at construction:

```php
$config = new Library([
    'app' => ['name' => 'demo'],
    'db'  => ['host' => 'localhost'],
]);
```

See [The Library Object](The-Library-Object.md).

## 3. The Config facade

`Config` forwards every static call to a single, process-wide `Library`
instance — register configuration once, read it anywhere:

```php
use InitPHP\Config\Config;

Config::setArray('site', ['url' => 'http://lvh.me']);

Config::get('site.url'); // 'http://lvh.me'
Config::has('site.url'); // true

Config::reset(); // discard the shared instance (useful in tests)
```

See [The Config Facade](The-Config-Facade.md).

## Loading from files and classes

The `Library` object (and therefore the `Config` facade) can import
configuration from several sources:

```php
// A PHP file that returns an array
Config::setFile('db', __DIR__ . '/config/db.php');

// Every *.php file in a directory, under a common prefix
Config::setDir('app', __DIR__ . '/config');

// A class's public properties
Config::setClass(\App\Config\Database::class);

Config::get('db.host');
Config::get('app.cache.driver');
Config::get('database.host');
```

See [Loaders](Loaders.md) for each method in depth.

## Reading values as objects

A `Library` exposes top-level entries as nested, read-only objects:

```php
$config = new Library();
$config->set('db.host', 'localhost')->set('db.user', 'root');

$config->db->host; // 'localhost'
$config->db->user; // 'root'
```

See [Object Access](Object-Access.md).

## Where to go next

- [Keys & Dotted Paths](Keys-and-Dotted-Paths.md) — how keys are parsed and normalised.
- [Loaders](Loaders.md) — `setArray`, `setFile`, `setDir`, `setClass`.
- [API Reference](API-Reference.md) — the exhaustive method list.
- [Exceptions](Exceptions.md) — what can go wrong, and how to handle it.
