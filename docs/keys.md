# Keys: Dotted Paths & Case-Insensitivity

Every entry point stores data in a single nested tree. Two rules govern
how a string key maps onto that tree.

## Dotted paths

A dot (`.`) separates levels of nesting. Writing a dotted key creates the
intermediate arrays automatically.

```php
use InitPHP\Config\Library;

$config = new Library();
$config->set('db.connections.mysql.host', 'localhost');

$config->get('db.connections.mysql.host'); // "localhost"
$config->get('db.connections.mysql');      // ['host' => 'localhost']
$config->get('db');                         // ['connections' => ['mysql' => ['host' => 'localhost']]]
```

Reading a path that stops at a scalar before consuming every segment
returns the default rather than descending into the scalar:

```php
$config->set('app.name', 'InitPHP');
$config->get('app.name.first', 'n/a'); // "n/a"
```

## Case-insensitivity

Keys are folded to lower-case internally, so the case you use when reading
does not have to match the case you used when writing.

```php
$config->set('App.Database.Host', 'localhost');

$config->get('app.database.host'); // "localhost"
$config->get('APP.DATABASE.HOST'); // "localhost"
$config->has('app.DATABASE.host'); // true
```

This applies to keys imported from files, arrays and classes too — for
example a file returning `['HOST' => 'localhost']` loaded under `db` is
read back as `db.host`.

## `null` values vs. missing keys

`has()` is the authoritative existence check: a key whose stored value is
`null` is still considered present.

```php
$config->set('feature.flag', null);

$config->has('feature.flag');          // true
$config->get('feature.flag', 'fallback'); // null  (the stored value wins)
$config->has('feature.missing');       // false
$config->get('feature.missing', 'fallback'); // "fallback"
```
