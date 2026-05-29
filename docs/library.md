# The Library Object

[`InitPHP\Config\Library`](../src/Library.php) is the package's main
entry point: an injectable object that holds a configuration tree and
knows how to populate it from several sources. Inject it where you need
isolated, testable configuration (as opposed to the global
[facade](facade.md)).

```php
use InitPHP\Config\Library;

$config = new Library();              // empty
$config = new Library(['db' => []]);  // seeded
```

## Loaders

### `setArray()`

```php
public function setArray(?string $name, array $assoc = []): self;
```

Import an associative array. With a `$name` the array is nested under that
key; with `null` or `''` it is **merged into the root** (existing keys are
preserved, colliding keys are overwritten).

```php
$config->setArray('site', [
    'url' => 'http://lvh.me',
    'db'  => ['host' => 'localhost', 'user' => 'db_user'],
]);

$config->get('site.url');     // "http://lvh.me"
$config->get('site.db.host'); // "localhost"

// Merge at the root:
$config->set('existing', 'kept');
$config->setArray(null, ['url' => 'http://lvh.me']);
$config->get('existing'); // "kept"
$config->get('url');      // "http://lvh.me"
```

### `setFile()`

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
$config->get('db.host'); // "localhost"  (keys folded to lower-case)
```

Throws [`ConfigException`](error-handling.md) when the file is missing,
does not have a `.php` extension, or does not return an array.

### `setDir()`

```php
public function setDir(?string $name, string $path, array $exclude = []): self;
```

Load every top-level `*.php` file in a directory. Each file is stored
under a key derived from its base name (`db.php` → `db`). A `$name`
becomes a common prefix. `$exclude` lists base names to skip — with or
without the `.php` suffix, matched case-insensitively.

```php
// config/db.php      -> ['HOST' => 'localhost']
// config/site.php    -> ['URL'  => 'http://lvh.me']
// config/secrets.php -> ['KEY'  => '...']

$config->setDir('app', __DIR__ . '/config', ['secrets']);

$config->get('app.db.host');  // "localhost"
$config->get('app.site.url'); // "http://lvh.me"
$config->has('app.secrets');  // false (excluded)
```

Throws [`ConfigException`](error-handling.md) when the path is not a
readable directory.

### `setClass()`

```php
public function setClass(string|object $classOrObject): self;
```

Import the **public** properties of a class or object under the class's
short name (namespace stripped). A class *name* imports property
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
$config->get('database.host'); // "localhost"

// By instance — runtime values:
$db = new \App\Config\Database();
$db->host = '10.0.0.5';
$config->setClass($db);
$config->get('database.host'); // "10.0.0.5"
```

Throws [`ConfigException`](error-handling.md) when a class name is given
but no such class exists.

> Unlike [`Classes`](configuration-classes.md), `setClass()` imports
> **public** properties only.

## Replacing everything

```php
public function replace(array $data): self;
```

`set()` writes a single key; `replace()` discards the whole tree and
installs a new one.

```php
$config->set('old', 'value');
$config->replace(['fresh' => 'data']);

$config->has('old');   // false
$config->get('fresh'); // "data"
```

## Object-style access

Top-level entries can be read as nested, read-only `stdClass` objects:

```php
$config->set('db.host', 'localhost')->set('db.user', 'root');

$config->db->host; // "localhost"
$config->db->user; // "root"

$config->name;     // a scalar entry is returned as-is
$config->unknown;  // null for an unknown key
```

## Lifecycle

```php
public function version(): string; // the package version, e.g. "2.0.0"
public function close(): void;      // clear the tree (the instance stays usable)
```

`close()` empties the configuration but keeps the instance ready for
reuse — the dotted-path and case-insensitive behaviour is preserved:

```php
$config->set('a.b', 'c');
$config->close();
$config->all();            // []
$config->set('x.y', 'z');  // still works
$config->get('x.y');       // "z"
```
