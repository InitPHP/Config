# Keys & Dotted Paths

Every entry point stores data in a single nested tree. Two rules govern
how a string key maps onto that tree. They are inherited from the
underlying [`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag)
store, which this package always configures in **multi mode** with
**case-insensitive** keys.

## Dotted paths

A dot (`.`) separates levels of nesting. Writing a dotted key creates the
intermediate arrays automatically.

```php
use InitPHP\Config\Library;

$config = new Library();
$config->set('db.connections.mysql.host', 'localhost');

$config->get('db.connections.mysql.host'); // 'localhost'
$config->get('db.connections.mysql');      // ['host' => 'localhost']
$config->get('db');                         // ['connections' => ['mysql' => ['host' => 'localhost']]]
```

Reading a path that hits a scalar before consuming every segment returns
the default rather than descending into the scalar:

```php
$config->set('app.name', 'InitPHP');
$config->get('app.name.first', 'n/a'); // 'n/a'
```

## Case-insensitivity

Keys are folded to lower-case internally, so the case you use when
reading does not have to match the case you used when writing.

```php
$config->set('App.Database.Host', 'localhost');

$config->get('app.database.host'); // 'localhost'
$config->get('APP.DATABASE.HOST'); // 'localhost'
$config->has('app.DATABASE.host'); // true
```

This applies to every source:

- A file returning `['HOST' => 'localhost']` loaded under `db` is read
  back as `db.host`.
- A class `AppConfig` loaded with [`setClass()`](Loaders.md#setclass) is
  read back under `appconfig`.

```php
// config/db.php  ->  return ['HOST' => 'localhost'];
$config->setFile('db', __DIR__ . '/config/db.php');
$config->get('db.host'); // 'localhost'
```

> **Why case-insensitive?** ParameterBag v2 is case-sensitive by default,
> but `initphp/config` opts into case-insensitivity so that file/class
> keys written in any casing (`HOST`, `Host`, `host`) resolve uniformly.
> This is configured internally and is not something callers turn off.

## `null` values vs. missing keys

`has()` is the authoritative existence check: a key whose stored value is
`null` is still considered present.

```php
$config->set('feature.flag', null);

$config->has('feature.flag');                 // true
$config->get('feature.flag', 'fallback');     // null  (the stored value wins)
$config->has('feature.missing');              // false
$config->get('feature.missing', 'fallback');  // 'fallback'
```

## Related reading

- [Loaders](Loaders.md) — how each source populates the tree.
- [Object Access](Object-Access.md) — reading a subtree as a nested object.
- ParameterBag's own [Nested Data](https://github.com/InitPHP/ParameterBag/wiki/Nested-Data)
  and [Case Sensitivity](https://github.com/InitPHP/ParameterBag/wiki/Case-Sensitivity)
  pages cover the underlying mechanics.
