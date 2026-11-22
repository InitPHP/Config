# Migration Guide (v1 → v2)

v2 is a major release. It fixes bugs that changed observable behaviour,
modernises the API, and tightens the type surface. Most everyday
`get`/`set`/`has` code keeps working unchanged; the changes below matter
mostly if you used the static facade as an object, relied on the
(broken) `setDir` prefix, or used `setArray`/`setFile` with a `null`
name.

## TL;DR

- **PHP 8.1+** is now required (was 7.4+). Version 3.0.0 raises the minimum again, to PHP 8.2.
- **`initphp/parameterbag` `^2.0`** is now required (was `^1.0`).
- **Case-insensitive keys still work** — the package opts into them
  internally, so reads/writes that worked in v1 keep working.
- The biggest source-level changes: the **facade is static-only**, and
  **`setArray`/`setFile` with a `null` name now merge** instead of
  replacing the whole tree.

## Requirements

| | v1 | v2 |
| --- | --- | --- |
| PHP | `>=7.4` | `^8.1` (`^8.2` from 3.0.0) |
| `initphp/parameterbag` | `^1.0` | `^2.0` |

ParameterBag v2 made keys case-sensitive by default. To preserve the
behaviour the v1 docs promised (e.g. loading `['HOST' => …]` and reading
`db.host`), `initphp/config` now configures its store with
`caseInsensitive => true` for you. **No action needed** — your
mixed-case keys still resolve.

## Behavioural changes (bug fixes)

### `setDir()` now applies the name prefix correctly

In v1 the prefix logic was inverted, so `setDir('app', …)` did **not**
produce `app.*` keys. v2 fixes it.

```php
$config->setDir('app', __DIR__ . '/config');

// v1: keys landed without the prefix (e.g. 'db.host')   ← bug
// v2: $config->get('app.db.host');  // correct
```

If you worked around the bug by reading un-prefixed keys, switch to the
prefixed form (or pass `null` as the name to keep no prefix).

### Object access (`$config->db`) now returns the full subtree

In v1, reading an array entry via property access collapsed every
element onto one property and returned only the last value. v2 builds a
correct, recursively nested `stdClass`.

```php
$config->set('db.host', 'localhost')->set('db.user', 'root');

// v1: $config->db  → only the last value survived          ← bug
// v2: $config->db->host;  // 'localhost'
//     $config->db->user;  // 'root'
```

See [Object Access](Object-Access.md).

### `setArray()` / `setFile()` with a `null` name now merge

In v1 a `null`/empty name **replaced the entire tree**. v2 **merges**
into the root instead, which is additive and far less surprising.

```php
$config->set('existing', 'kept');
$config->setArray(null, ['url' => 'http://lvh.me']);

// v1: 'existing' was wiped (whole tree replaced)
// v2: $config->get('existing'); // 'kept'  — merged at root
```

If you depended on the replace-all behaviour, use the new
[`replace()`](The-Library-Object.md#replacing-the-whole-tree) method.

### `setClass()` with an object uses runtime values

Given an instance, v2 imports the object's **current** public property
values (v1 imported class defaults in both cases).

```php
$db = new Database();
$db->host = '10.0.0.5';
$config->setClass($db);

// v2: $config->get('database.host'); // '10.0.0.5'
```

## API changes

### The `Config` facade is static-only

v1's facade exposed an instance API (`__construct`, `__destruct`,
`__get`, `__call`, `__debugInfo`). That coupling could leave the shared
singleton in an unusable state. v2 removes the instance API: `Config` is
a pure static utility with a `private` constructor.

```php
// v1 (no longer supported)
$c = new Config();
$c->get('x');

// v2
Config::get('x');
Config::reset(); // discard the shared instance (e.g. between tests)
```

If you held a `Config` instance, switch to static calls, or inject a
[`Library`](The-Library-Object.md) where you want an object.

### `remove()` is available everywhere

In v1 `remove()` existed only on `Classes`. v2 implements it on the
shared base, so [`Library`](The-Library-Object.md) and the
[`Config` facade](The-Config-Facade.md) expose it too.

```php
Config::remove('app.debug');
$library->remove('db.pass');
```

### `Library::set(null, …)` removed

v1 let you call `set(null, $array)` as a replace-all shortcut. v2's
`set()` matches the interface (`string $key`); use
[`replace()`](The-Library-Object.md#replacing-the-whole-tree) instead.

### Loaders throw `ConfigException`

v1 loaders threw a generic `\Exception`. v2 throws
[`ConfigException`](Exceptions.md) (a `\RuntimeException`). Broaden or
update your catch blocks:

```php
// v1
catch (\Exception $e) { /* … */ }

// v2 (more specific)
catch (\InitPHP\Config\Exceptions\ConfigException $e) { /* … */ }
```

## New in v2

- [`Library::replace()`](The-Library-Object.md#replacing-the-whole-tree) — discard and reinstall the tree.
- [`Config::reset()`](The-Config-Facade.md#resetting-between-runs) — drop the shared instance.
- [`ConfigException`](Exceptions.md) — a dedicated, well-messaged exception type.
- `AbstractConfig` — a shared base behind `Library` and `Classes`.
- A test suite, PHPStan (level 8), PHP-CS-Fixer, and CI across PHP 8.1–8.4 (at the time of 2.0.0).

## Upgrade checklist

1. Bump your platform to **PHP 8.2+** (2.x runs on 8.1, but 3.0.0 requires 8.2).
2. `composer require initphp/config:^3.0` (pulls `parameterbag:^3.0`).
3. Replace any `new Config()` usage with static `Config::` calls.
4. Audit `setArray(null, …)` / `setFile(null, …)` — they now merge; use
   `replace()` if you wanted replace-all.
5. Verify `setDir(name, …)` consumers now read **prefixed** keys.
6. Update loader `catch` blocks to `ConfigException` (or
   `\RuntimeException`).

## Related reading

- [The Config Facade](The-Config-Facade.md)
- [Loaders](Loaders.md)
- [Exceptions](Exceptions.md)
- ParameterBag's own [Migration Guide](https://github.com/InitPHP/ParameterBag/wiki/Migration-Guide).
