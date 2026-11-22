# Loaders

The [`Library`](The-Library-Object.md) object — and therefore the
[`Config` facade](The-Config-Facade.md) — can import configuration from four
sources. Every loader returns `$this` for chaining and throws
[`ConfigException`](Exceptions.md) on failure.

All keys land in the tree case-insensitively; see
[Keys & Dotted Paths](Keys-and-Dotted-Paths.md).

---

## `setArray()`

```php
public function setArray(?string $name, array $assoc = []): self;
```

Import an associative array. With a `$name` the array is nested under
that key; with `null` or `''` it is **merged into the root** (existing
keys are preserved, colliding keys are overwritten).

```php
$config->setArray('site', [
    'url' => 'http://lvh.me',
    'db'  => ['host' => 'localhost', 'user' => 'db_user'],
]);

$config->get('site.url');     // 'http://lvh.me'
$config->get('site.db.host'); // 'localhost'
```

Merge at the root:

```php
$config->set('existing', 'kept');
$config->setArray(null, ['url' => 'http://lvh.me']);

$config->get('existing'); // 'kept'
$config->get('url');      // 'http://lvh.me'
```

> **Replace vs. merge:** a `null`/empty name **merges** into the root. If
> you want to discard everything and start over, use
> [`replace()`](The-Library-Object.md#replacing-the-whole-tree) instead.

---

## `setFile()`

```php
public function setFile(?string $name, string $path): self;
```

Load a PHP file that **returns** an associative array. As with
`setArray()`, a `null`/`''` name merges into the root.

```php
// config/db.php
return [
    'HOST' => 'localhost',
    'USER' => 'root',
];
```

```php
$config->setFile('db', __DIR__ . '/config/db.php');

$config->get('db.host'); // 'localhost'  (keys folded to lower-case)
$config->get('db.user'); // 'root'
```

Throws [`ConfigException`](Exceptions.md) when the file:

- does not exist,
- does not have a `.php` extension, or
- does not return an array.

---

## `setDir()`

```php
public function setDir(?string $name, string $path, array $exclude = []): self;
```

Load every top-level `*.php` file in a directory. Each file is stored
under a key derived from its base name (`db.php` → `db`). A `$name`
becomes a common prefix. `$exclude` lists base names to skip — with or
without the `.php` suffix, matched case-insensitively.

```php
// config/db.php      ->  return ['HOST' => 'localhost'];
// config/site.php    ->  return ['URL'  => 'http://lvh.me'];
// config/secrets.php ->  return ['KEY'  => '...'];

$config->setDir('app', __DIR__ . '/config', ['secrets']);

$config->get('app.db.host');  // 'localhost'
$config->get('app.site.url'); // 'http://lvh.me'
$config->has('app.secrets');  // false (excluded)
```

Without a name, the files land at the root:

```php
$config->setDir(null, __DIR__ . '/config');

$config->get('db.host');  // 'localhost'
$config->get('site.url'); // 'http://lvh.me'
```

Throws [`ConfigException`](Exceptions.md) when the path is not a directory,
or when a loaded file does not return an array.

> **Every `*.php` file is loaded.** Keep non-config PHP out of the
> directory, or list it in `$exclude`. A file that does not return an
> array aborts the whole call with an exception.

---

## `setClass()`

```php
public function setClass(string|object $classOrObject): self;
```

Import the **public** properties of a class or object under the class's
short name (its namespace stripped). A class *name* imports property
*defaults*; an *instance* imports the *current* values.

```php
namespace App\Config;

class Database
{
    public string $host = 'localhost';
    public int    $port = 3306;
}
```

```php
// By class name — defaults:
$config->setClass(\App\Config\Database::class);
$config->get('database.host'); // 'localhost'
$config->get('database.port'); // 3306

// By instance — runtime values:
$db = new \App\Config\Database();
$db->host = '10.0.0.5';
$config->setClass($db);
$config->get('database.host'); // '10.0.0.5'
```

Throws [`ConfigException`](Exceptions.md) when a class *name* is given but
no such class exists.

> **Public only.** Unlike [`Classes`](Configuration-Classes.md), which
> imports public **and protected** properties, `setClass()` reads only
> the **public** surface — it inspects the target from the outside.

---

## Composing sources

Loaders chain, and later writes win on key collisions, so you can layer
a base file under environment-specific overrides:

```php
$config = new Library();

$config
    ->setDir('app', __DIR__ . '/config')                  // base
    ->setFile('app', __DIR__ . '/config/local.php');      // overrides merged on top
```

For a full worked example see the
[Environment-Aware Config recipe](Recipe-Environment-Aware-Config.md).

## Related reading

- [Keys & Dotted Paths](Keys-and-Dotted-Paths.md) — key parsing and casing.
- [Exceptions](Exceptions.md) — every failure mode, with messages.
- [API Reference](API-Reference.md#library) — signatures and return types.
