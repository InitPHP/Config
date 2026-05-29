# Configuration Classes

[`InitPHP\Config\Classes`](../src/Classes.php) is an abstract base class
that turns a subclass's properties into a configuration tree. It is the
most natural fit for configuration that ships with your application as
code.

## Defining a config class

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

$config->get('url');     // "http://lvh.me"
$config->get('name');    // "LocalHost"
$config->get('db.host'); // "localhost"  (nested array → dotted path)
$config->get('db.user'); // "root"

$config->has('URL');     // true (case-insensitive)
$config->all();          // ['url' => ..., 'name' => ..., 'db' => [...]]
```

## Updating at runtime

A config class is not frozen — you can `set` and `remove` after
construction:

```php
$config->set('db.pass', 'secret');
$config->get('db.pass'); // "secret"

$config->remove('db.pass');
$config->has('db.pass'); // false
```

## What gets imported

- The property **default values** declared on the class are imported.
- Both **public and protected** properties are included (they are visible
  from the `Classes` base in the inheritance chain); **private**
  properties of the subclass are not.
- The internal `parameterBag` infrastructure property is **never** part of
  the tree.

```php
$config->has('parameterBag'); // false
```

> **Note:** this differs from [`Library::setClass()`](library.md#setclass),
> which imports **public** properties only. The difference is intentional:
> `Classes` inspects itself from within the hierarchy, whereas
> `setClass()` inspects an unrelated class from the outside.
