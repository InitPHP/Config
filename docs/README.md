# InitPHP Config — Documentation

Developer documentation for the `initphp/config` package. Each guide is
self-contained and every example is runnable against the current API.

## Contents

1. [Getting Started](getting-started.md) — install, the three entry points, your first read/write.
2. [Keys: Dotted Paths & Case-Insensitivity](keys.md) — how keys are parsed and normalised.
3. [Configuration Classes](configuration-classes.md) — the `Classes` base class.
4. [The Library Object](library.md) — the full loader API (`setArray`, `setFile`, `setDir`, `setClass`) plus object access.
5. [The Static Facade](facade.md) — the process-wide `Config` singleton.
6. [Error Handling](error-handling.md) — `ConfigException` and its failure modes.

## At a glance

```php
use InitPHP\Config\Config;
use InitPHP\Config\Library;

// Static facade — shared, process-wide instance.
Config::setArray('db', ['host' => 'localhost']);
Config::get('db.host'); // "localhost"

// Injectable object — isolated instance.
$config = new Library(['db' => ['host' => '127.0.0.1']]);
$config->get('db.host'); // "127.0.0.1"
```

## The shared contract

`Classes`, `Library`, and the `Config` facade all expose the same
read/write surface defined by
[`ConfigInterface`](../src/Interfaces/ConfigInterface.php):

| Method | Returns | Notes |
| ------ | ------- | ----- |
| `get(string $key, mixed $default = null)` | `mixed` | `$default` when the key is absent. |
| `set(string $key, mixed $value)` | `self` | Creates intermediate arrays on demand. |
| `has(string $key)` | `bool` | A stored `null` still counts as present. |
| `remove(string $key)` | `self` | No-op when the key is absent. |
| `all()` | `array` | The whole tree as a plain array. |
