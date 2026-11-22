# Recipe: Database Config

**Goal:** keep database credentials in a dedicated PHP file, load them
through the package, and read them safely when opening a connection.

## The file

```php
// config/db.php
return [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'name'    => 'app',
    'user'    => 'app',
    'pass'    => '',
    'charset' => 'utf8mb4',
];
```

## Loading and reading

```php
use InitPHP\Config\Library;

$config = new Library();
$config->setFile('db', __DIR__ . '/config/db.php');

$config->get('db.host');             // '127.0.0.1'
$config->get('db.port');             // 3306
$config->get('db.charset', 'utf8');  // 'utf8mb4'
```

Keys are case-insensitive, so a file written with upper-case keys
(`'HOST' => …`) is still read as `db.host`. See
[Keys & Dotted Paths](Keys-and-Dotted-Paths.md).

## Opening a PDO connection

Read each value with a sensible default at the point of use — that is
where validation belongs:

```php
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $config->get('db.host', '127.0.0.1'),
    (int) $config->get('db.port', 3306),
    $config->get('db.name', 'app'),
    $config->get('db.charset', 'utf8mb4'),
);

$pdo = new PDO(
    $dsn,
    $config->get('db.user', 'root'),
    $config->get('db.pass', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);
```

## Reading the block as an object

If you prefer object syntax, pull the whole subtree via
[object access](Object-Access.md) (Library only):

```php
$db = $config->db; // stdClass

$db->host; // '127.0.0.1'
$db->name; // 'app'
```

## Keeping secrets out of the repo

Commit a safe `db.php` with defaults, and merge a git-ignored local file
on top for real credentials. To **merge** (rather than replace) the `db`
block, load the override at the **root** with a `null` name and nest the
keys under `db` inside the file:

```php
// config/db.local.php   (git-ignored)
return [
    'db' => [
        'pass' => 'realsecret', // only the keys you override
    ],
];
```

```php
use InitPHP\Config\Exceptions\ConfigException;

$config->setFile('db', __DIR__ . '/config/db.php'); // committed defaults under 'db'

try {
    // null name → recursive merge at the root, so 'db.*' siblings survive
    $config->setFile(null, __DIR__ . '/config/db.local.php');
} catch (ConfigException) {
    // No local overrides present — defaults stand.
}

$config->get('db.pass'); // 'realsecret'
$config->get('db.host'); // '127.0.0.1'  (committed default preserved)
```

> **Why not load the override under the `db` name again?** Loading a file
> under a **named** key (`setFile('db', …)`) **replaces** the entire `db`
> subtree. Recursive merging only happens for a `null`/empty name, which
> merges at the root — hence the nested `['db' => …]` shape above. See
> [Loaders → setArray](Loaders.md#setarray).

## Related reading

- [Loaders → setFile](Loaders.md#setfile)
- [Object Access](Object-Access.md)
- [Exceptions](Exceptions.md) — handling a missing or malformed file.
