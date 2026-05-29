# InitPHP Config

Advanced configuration manager for PHP: a single configuration tree with
dotted-path access, case-insensitive keys, and loaders for arrays, PHP
files, whole directories and class/object properties — usable either as
an injectable object or through a static facade.

[![CI](https://github.com/InitPHP/Config/actions/workflows/ci.yml/badge.svg)](https://github.com/InitPHP/Config/actions/workflows/ci.yml)
[![Latest Stable Version](http://poser.pugx.org/initphp/config/v)](https://packagist.org/packages/initphp/config)
[![Total Downloads](http://poser.pugx.org/initphp/config/downloads)](https://packagist.org/packages/initphp/config)
[![License](http://poser.pugx.org/initphp/config/license)](https://packagist.org/packages/initphp/config)
[![PHP Version Require](http://poser.pugx.org/initphp/config/require/php)](https://packagist.org/packages/initphp/config)

## Requirements

- PHP 8.1 or higher
- [initphp/parameterbag](https://github.com/InitPHP/ParameterBag) `^2.0`

## Installation

```bash
composer require initphp/config
```

## Key Concepts

- **Dotted paths** — `db.host` addresses `host` inside the `db` array. Keys
  can be nested arbitrarily deep.
- **Case-insensitive keys** — `DB.Host`, `db.host` and `Db.HOST` all refer
  to the same entry. Keys are folded to lower-case internally.
- **Three entry points** that all share the same read/write contract
  ([`ConfigInterface`](src/Interfaces/ConfigInterface.php)):
  - [`Classes`](src/Classes.php) — turn a class's public properties into configuration.
  - [`Library`](src/Library.php) — an injectable object with the full loader API.
  - [`Config`](src/Config.php) — a static facade over a shared `Library`.

## Quick Start

### Configuration classes

Declare your configuration as public properties and read it through the
shared API:

```php
use InitPHP\Config\Classes;

final class MyAppConfig extends Classes
{
    public string $url  = 'http://lvh.me';
    public string $name = 'LocalHost';

    /** @var array<string, string> */
    public array $db = [
        'host' => 'localhost',
        'user' => 'root',
    ];
}

$config = new MyAppConfig();

$config->get('url');                 // "http://lvh.me"
$config->get('db.host');             // "localhost"
$config->get('details', 'Not Found'); // "Not Found"

if ($config->has('name')) {
    $config->get('name');            // "LocalHost"
}
```

### The `Library` object

```php
use InitPHP\Config\Library;

$config = new Library();

$config->set('site.url', 'http://lvh.me')
       ->set('site.db.host', 'localhost');

$config->get('site.url');     // "http://lvh.me"
$config->get('SITE.DB.HOST'); // "localhost" (case-insensitive)
```

### The `Config` static facade

Every call is forwarded to a lazily created, process-wide `Library`
singleton:

```php
use InitPHP\Config\Config;

Config::setArray('site', ['url' => 'http://lvh.me']);

Config::get('site.url'); // "http://lvh.me"
Config::has('site.url'); // true

Config::reset(); // discard the shared instance (useful in tests)
```

## Reading and writing

The following methods are available on `Classes`, `Library`, and the
`Config` facade alike:

| Method | Description |
| ------ | ----------- |
| `get(string $key, mixed $default = null): mixed` | Read a value, or `$default` when the key is absent. |
| `set(string $key, mixed $value): self` | Write a value (intermediate arrays are created as needed). |
| `has(string $key): bool` | Whether the key exists (a stored `null` still counts as present). |
| `remove(string $key): self` | Remove a key (a no-op when it is absent). |
| `all(): array` | The entire configuration tree as a plain array. |

## Loaders (`Library` / `Config`)

### `setArray()`

```php
public function setArray(?string $name, array $assoc = []): self;
```

Imports an associative array. When `$name` is `null` or `''` the array is
**merged into the root**; otherwise it is stored under `$name`.

```php
Config::setArray('site', [
    'url' => 'http://lvh.me',
    'db'  => ['host' => 'localhost', 'user' => 'db_user'],
]);

Config::get('site.url');     // "http://lvh.me"
Config::get('site.db.host'); // "localhost"
```

### `setFile()`

```php
public function setFile(?string $name, string $path): self;
```

Loads a PHP file that **returns** an associative array.

```php
// config/db.php
return [
    'HOST' => 'localhost',
    'USER' => 'root',
];
```

```php
Config::setFile('db', __DIR__ . '/config/db.php');

Config::get('db.host'); // "localhost"  (keys are case-insensitive)
```

### `setDir()`

```php
public function setDir(?string $name, string $path, array $exclude = []): self;
```

Loads every top-level `*.php` file in a directory. Each file is stored
under a key derived from its base name; when `$name` is given it becomes a
common prefix. Files can be skipped via `$exclude` (with or without the
`.php` suffix).

```php
// config/db.php   -> returns ['HOST' => 'localhost']
// config/site.php -> returns ['URL'  => 'http://lvh.me']

Config::setDir('app', __DIR__ . '/config', ['secrets']);

Config::get('app.db.host'); // "localhost"
Config::get('app.site.url'); // "http://lvh.me"
```

### `setClass()`

```php
public function setClass(string|object $classOrObject): self;
```

Imports the **public** properties of a class or object under the class's
short name. A class name imports the property *defaults*; an instance
imports the *current* values.

```php
namespace App\Config;

class AppConfig
{
    public string $url = 'http://lvh.me';
}

class Database
{
    public string $host = 'localhost';
}
```

```php
Config::setClass(\App\Config\AppConfig::class); // by class name
Config::setClass(new \App\Config\Database());   // by instance

Config::get('appconfig.url'); // "http://lvh.me"
Config::get('database.host'); // "localhost"
```

## Object-style access (`Library`)

A `Library` exposes top-level entries as read-only nested objects:

```php
$config = new Library();
$config->set('db.host', 'localhost')->set('db.user', 'root');

$config->db->host; // "localhost"
$config->db->user; // "root"
```

## Error handling

Loader failures throw
[`InitPHP\Config\Exceptions\ConfigException`](src/Exceptions/ConfigException.php)
(a `RuntimeException`): missing files, files that do not return an array,
invalid directories, and unknown class names.

```php
use InitPHP\Config\Exceptions\ConfigException;

try {
    Config::setFile('db', '/path/to/missing.php');
} catch (ConfigException $e) {
    // "Configuration file "/path/to/missing.php" was not found."
}
```

## Documentation

In-depth guides with runnable examples live in [`docs/`](docs/README.md).

## Testing

```bash
composer test      # PHPUnit
composer analyse   # PHPStan (level 8)
composer cs:check  # PHP-CS-Fixer (dry-run)
```

## Credits

- [Muhammet ŞAFAK](https://www.muhammetsafak.com.tr) — <info@muhammetsafak.com.tr>

## License

Released under the [MIT License](LICENSE). Copyright &copy; InitPHP.
