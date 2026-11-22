# Configuration Classes

`InitPHP\Config\Classes` is an abstract base class that turns a
subclass's properties into a configuration tree. It is the most natural
fit for configuration that ships with your application **as code** —
typed, discoverable, and version-controlled alongside the rest of your
source.

## Defining a config class

Extend `Classes` and declare your configuration as properties with
default values:

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
```

## Reading it back

```php
$config = new AppConfig();

$config->get('url');     // 'http://lvh.me'
$config->get('name');    // 'LocalHost'
$config->get('db.host'); // 'localhost'  (nested array → dotted path)
$config->get('db.user'); // 'root'

$config->has('URL');     // true (case-insensitive)
$config->all();          // ['url' => ..., 'name' => ..., 'db' => [...]]
```

## Updating at runtime

A config class is not frozen. Because it shares the
[`ConfigInterface`](API-Reference.md#configinterface) surface, you can
`set` and `remove` after construction:

```php
$config->set('db.pass', 'secret');
$config->get('db.pass'); // 'secret'

$config->remove('db.pass');
$config->has('db.pass'); // false
```

## What gets imported

- The property **default values** declared on the class are imported.
- Both **public and protected** properties are included — they are
  visible from the `Classes` base in the inheritance chain. A subclass's
  **private** properties are **not** imported.
- The internal `parameterBag` infrastructure property is **never** part
  of the tree.

```php
final class WithVisibility extends Classes
{
    public string    $publicValue    = 'public';
    protected string $protectedValue = 'protected';
    private string   $privateValue   = 'private';
}

$config = new WithVisibility();

$config->get('publicValue');    // 'public'
$config->get('protectedValue'); // 'protected'
$config->has('privateValue');   // false
$config->has('parameterBag');   // false
```

> **Note:** this differs from
> [`Library::setClass()`](Loaders.md#setclass), which imports **public**
> properties only. The difference is intentional: `Classes` inspects
> itself from within the hierarchy, whereas `setClass()` inspects an
> unrelated class from the outside.

## How it works

`Classes::__construct()` reads the declared property defaults of the
concrete subclass (`get_class_vars(static::class)`), drops the internal
infrastructure property, and seeds the underlying store in dotted-path,
case-insensitive mode. You do not call `parent::__construct()` with any
arguments — just extend and declare properties.

## When to use it

| Use `Classes` when… | Reach for [`Library`](The-Library-Object.md) when… |
| --- | --- |
| Config is known at author time. | Config is loaded from files/env at runtime. |
| You want IDE autocompletion and types. | Sources are dynamic (directories, env). |
| Config is small and stable. | You compose many sources into one tree. |

## Related reading

- [Keys & Dotted Paths](Keys-and-Dotted-Paths.md) — how nested arrays become dotted keys.
- [Typed Config Class recipe](Recipe-Typed-Config-Class.md) — a fuller example.
- [API Reference](API-Reference.md#classes) — the class surface.
