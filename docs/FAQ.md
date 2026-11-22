# FAQ

Quick answers to questions that come up when adopting the package. If
your question is not here, please
[open an issue](https://github.com/InitPHP/Config/issues/new) —
documentation fixes are reviewed eagerly.

## General

### What is `initphp/config` for?

A single, uniform API over configuration loaded from arrays, PHP files,
directories, and class/object properties — with dotted-path access and
case-insensitive keys. Use it for application settings, database/cache
config, feature flags, and similar.

### How is it different from ParameterBag?

[`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag) is the
low-level nested store. `initphp/config` is built **on top of it** and
adds: file/directory/class loaders, a static facade, object-style access,
and a default-on case-insensitive, dotted-path configuration. If you only
need a key/value bag, use ParameterBag directly; if you need to *load*
configuration from multiple sources, use this package.

### Which should I use — `Classes`, `Library`, or `Config`?

| You want… | Use |
| --- | --- |
| Typed, code-defined config | [`Classes`](Configuration-Classes.md) |
| An injectable object loaded at runtime | [`Library`](The-Library-Object.md) |
| Global access with no wiring | [`Config`](The-Config-Facade.md) facade |

They share the same read/write contract, so you can start with one and
move to another with minimal changes.

## Usage

### Why does `Config::get('db.host')` work even though I stored `HOST`?

Keys are case-insensitive in this package — they are folded to
lower-case internally. `HOST`, `Host`, and `host` all resolve to the same
entry. See [Keys & Dotted Paths](Keys-and-Dotted-Paths.md).

### Why did `setArray(null, …)` not wipe my existing config?

In v2 a `null`/empty name **merges into the root** rather than replacing
the whole tree. If you want to discard everything, call
[`replace()`](The-Library-Object.md#replacing-the-whole-tree). (This changed
from v1 — see the [Migration Guide](Migration-Guide.md).)

### Why can't I do `new Config()`?

The `Config` facade is a static-only utility with a `private`
constructor. Use the static methods (`Config::get(...)`), or inject a
[`Library`](The-Library-Object.md) when you need an object.

### My `setDir('app', …)` keys are under `app.*` now — did something change?

Yes. v1 had an inverted prefix bug that dropped the name. v2 applies the
prefix correctly, so files load under `app.<file>`. Pass `null` as the
name if you want no prefix. See the [Migration Guide](Migration-Guide.md).

### Why does `setClass()` skip my protected/private properties?

[`setClass()`](Loaders.md#setclass) reads **public** properties only,
because it inspects the target class from the outside. The
[`Classes`](Configuration-Classes.md) base — which inspects itself from
within the hierarchy — imports public **and** protected properties (but
not private).

### How do I read a whole subtree at once?

Either `get('db')` to receive the array, or use
[object access](Object-Access.md): `$library->db->host`.

### How do I distinguish "set to null" from "not set"?

Use `has()`. A key whose value is `null` is still considered present;
`get()` will return that `null` rather than your default. See
[Keys & Dotted Paths](Keys-and-Dotted-Paths.md#null-values-vs-missing-keys).

### How do I reset configuration between tests?

Call `Config::reset()` in your test's `setUp()` (or `tearDown()`). It
discards the shared singleton so the next test starts clean. For a
[`Library`](The-Library-Object.md), just create a new instance per test —
no global state is involved.

## Behaviour & limits

### Does it validate configuration values?

No. The package is a loader and accessor; values flow through unchanged.
Validate at the consumer boundary (e.g. when reading `db.port`, cast and
range-check it there).

### Is the configuration immutable?

No. Both `Library` and `Classes` mutate in place. If you need read-only
config after boot, wrap the instance in your own decorator that exposes
only `get`/`has`/`all`.

### Is the `Config` facade thread/async safe?

It is a process-wide singleton; treat it like any shared global. In a
worker that handles many requests in one process, call `Config::reset()`
(or reload) between requests if request-specific config must not leak.

### Does loading a directory load *every* PHP file?

Yes — every top-level `*.php` file. Each must `return` an array, or the
call throws. Keep non-config PHP out of the directory, or list it in the
`$exclude` argument. See [Loaders → setDir](Loaders.md#setdir).

## Related reading

- [Quick Start](Quick-Start.md)
- [Loaders](Loaders.md)
- [Migration Guide](Migration-Guide.md)
- [Exceptions](Exceptions.md)
