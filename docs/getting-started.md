# Getting Started

## Installation

```bash
composer require initphp/config
```

Requirements: **PHP 8.1+** and [`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag) `^2.0`
(installed automatically by Composer).

## The three entry points

The package gives you three ways to work with the same configuration
model. They all implement
[`ConfigInterface`](../src/Interfaces/ConfigInterface.php), so the
`get`/`set`/`has`/`remove`/`all` surface is identical everywhere.

| Entry point | Best for | Lifetime |
| ----------- | -------- | -------- |
| [`Classes`](configuration-classes.md) | Strongly-typed, code-defined config | Per instance |
| [`Library`](library.md) | Dependency-injected config you load at runtime | Per instance |
| [`Config`](facade.md) | Quick global access without wiring | Process-wide singleton |

## Your first read and write

```php
use InitPHP\Config\Library;

$config = new Library();

$config->set('app.name', 'InitPHP')
       ->set('app.debug', true);

$config->get('app.name');           // "InitPHP"
$config->get('app.debug');          // true
$config->get('app.locale', 'en');   // "en" (default — key absent)
$config->has('app.name');           // true
$config->all();                     // ['app' => ['name' => 'InitPHP', 'debug' => true]]
```

## Seeding data at construction

`Library` accepts an initial tree:

```php
$config = new Library([
    'app' => ['name' => 'InitPHP'],
    'db'  => ['host' => 'localhost'],
]);

$config->get('db.host'); // "localhost"
```

## Next steps

- Learn how keys are parsed in [Keys: Dotted Paths & Case-Insensitivity](keys.md).
- Load configuration from files and directories in [The Library Object](library.md).
- Use the package globally with [The Static Facade](facade.md).
