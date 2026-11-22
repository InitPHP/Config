# Object Access

A [`Library`](The-Library-Object.md) exposes top-level configuration entries
as **read-only nested objects** through PHP's magic property access. It
is a convenience for reading deep config without dotted strings.

> Object access is a feature of `Library` only. The
> [`Classes`](Configuration-Classes.md) base does not provide it, and the
> [`Config` facade](The-Config-Facade.md) is static — there is no instance
> to read properties from.

## Reading a subtree as an object

```php
use InitPHP\Config\Library;

$config = new Library();
$config->set('db.host', 'localhost')
       ->set('db.user', 'root');

$db = $config->db;       // stdClass

$db->host;               // 'localhost'
$db->user;               // 'root'
```

Nesting works to any depth:

```php
$config->set('services.cache.driver', 'redis');

$config->services->cache->driver; // 'redis'
```

## Scalars and missing keys

- A **scalar** top-level entry is returned as-is.
- An **unknown** key returns `null`.

```php
$config->set('name', 'InitPHP');

$config->name;    // 'InitPHP'  (scalar, returned directly)
$config->unknown; // null
```

## How it works

`Library::__get($name)` looks the key up in the underlying store. If the
value is an array it is converted, recursively, into a nested
`stdClass`; scalars pass through unchanged; an absent key yields `null`.

```php
// Equivalent ways to read the same value:
$config->get('db.host'); // via the dotted-path API
$config->db->host;       // via object access
```

## Casing

Because the store is case-insensitive, the object's property names are
the **lower-cased** form of the keys:

```php
$config->set('DB.Host', 'localhost');

$config->db->host; // 'localhost'
// $config->DB->Host would not match — keys are normalised to lower-case.
```

## It is a snapshot, not a live view

`__get` builds a fresh object from the current data each time it is
called. Mutating the returned object does **not** write back into the
configuration:

```php
$config->set('db.host', 'localhost');

$db = $config->db;
$db->host = 'changed';        // touches the local copy only

$config->get('db.host');      // 'localhost'  (unchanged)
```

To change configuration, use [`set()`](The-Library-Object.md#the-readwrite-surface).

## Related reading

- [The Library Object](The-Library-Object.md) — the full object API.
- [Keys & Dotted Paths](Keys-and-Dotted-Paths.md) — the dotted-string equivalent.
