# Recipe: Environment-Aware Config

**Goal:** load a base configuration directory, then merge
environment-specific overrides (e.g. `local`, `production`) on top, so a
single tree reflects the active environment.

## The layout

```
config/
├── app.php          # base defaults
├── db.php           # base defaults
└── environments/
    ├── local.php    # overrides for local
    └── production.php
```

```php
// config/app.php
return [
    'name'     => 'InitPHP App',
    'debug'    => false,
    'timezone' => 'UTC',
];

// config/db.php
return [
    'host' => '127.0.0.1',
    'name' => 'app',
    'user' => 'app',
    'pass' => '',
];

// config/environments/local.php
return [
    'app' => ['debug' => true],
    'db'  => ['pass' => 'devsecret'],
];
```

## Loading it

Load the base directory first, then merge the environment file at the
root. Because [`setFile()`](Loaders.md#setfile) with a `null` name **merges
recursively**, sibling keys are preserved and only the overridden leaves
change.

```php
use InitPHP\Config\Exceptions\ConfigException;
use InitPHP\Config\Library;

$env = getenv('APP_ENV') ?: 'production';

$config = new Library();
$config->setDir(null, __DIR__ . '/config', ['environments']); // base, skip the env folder

try {
    $config->setFile(null, __DIR__ . "/config/environments/{$env}.php"); // overrides merged at root
} catch (ConfigException) {
    // No override file for this environment — base config stands.
}

$config->get('app.debug'); // true  on local, false otherwise
$config->get('db.pass');   // 'devsecret' on local
$config->get('db.host');   // '127.0.0.1'  (base value preserved)
$config->get('app.name');  // 'InitPHP App' (base value preserved)
```

> `setDir(null, …)` loads each `*.php` file under its base name
> (`app.php` → `app`, `db.php` → `db`). The `['environments']` exclude
> keeps the per-environment **folder** name out of the scan — folders are
> ignored by `setDir`, but excluding it documents intent and guards a
> stray `environments.php`.

## Sharing it globally

If you prefer global access, register the same tree on the
[facade](The-Config-Facade.md) during bootstrap:

```php
use InitPHP\Config\Config;

Config::setDir(null, __DIR__ . '/config', ['environments']);
Config::setFile(null, __DIR__ . "/config/environments/{$env}.php");

// Anywhere later:
Config::get('app.timezone');
```

## Why merge (not replace)?

A `null`/empty name on `setArray`/`setFile` performs a **recursive
merge**, so an override file only needs to specify the keys it changes.
Replacing would force every environment file to restate the entire tree.
If you ever do want a clean slate, call
[`replace()`](The-Library-Object.md#replacing-the-whole-tree).

## Related reading

- [Loaders](Loaders.md) — `setDir` and `setFile` in detail.
- [Keys & Dotted Paths](Keys-and-Dotted-Paths.md) — how merged keys resolve.
- [Exceptions](Exceptions.md) — handling a missing override file.
