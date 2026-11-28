# InitPHP Config — Wiki

Welcome to the official documentation for **`initphp/config`** — an
advanced configuration manager that gives PHP applications a single,
uniform API over configuration loaded from **arrays**, **PHP files**,
**whole directories**, and **class/object properties**, with
**dotted-path** access and **case-insensitive** keys.

It is built on top of [`initphp/parameterbag`](https://github.com/InitPHP/ParameterBag),
which provides the underlying nested store.

The package ships three entry points that all share the same read/write
contract:

| Type | Purpose |
| --- | --- |
| [`Classes`](Configuration-Classes.md) | Turn a class's own properties into configuration. |
| [`Library`](The-Library-Object.md) | An injectable object with the full loader API. |
| [`Config`](The-Config-Facade.md) | A static facade over a shared `Library` singleton. |

All three implement [`ConfigInterface`](API-Reference.md#configinterface), so
`get` / `set` / `has` / `remove` / `all` behave identically wherever you
use them.

```bash
composer require initphp/config
```

```php
use InitPHP\Config\Config;

Config::setArray('app', [
    'name' => 'demo',
    'db'   => ['host' => 'localhost', 'user' => 'root'],
]);

Config::get('app.name');               // 'demo'
Config::get('app.db.host');            // 'localhost'
Config::get('app.db.charset', 'utf8'); // 'utf8' (default — key absent)
Config::get('APP.DB.HOST');            // 'localhost' (case-insensitive)
```

## Start here

- **New to the package?** Read [Installation](Installation.md), then [Quick Start](Quick-Start.md).
- **Defining config as code?** See [Configuration Classes](Configuration-Classes.md).
- **Loading from files/dirs?** See [Loaders](Loaders.md) and the
  [Environment-Aware Config recipe](Recipe-Environment-Aware-Config.md).
- **Want global access?** Read [The Config Facade](The-Config-Facade.md).
- **Curious how keys are parsed?** Read [Keys & Dotted Paths](Keys-and-Dotted-Paths.md).
- **Upgrading from v1?** Read the [Migration Guide](Migration-Guide.md).
- **Looking for a specific method?** The full [API Reference](API-Reference.md)
  lists every class member.

## At a glance — entry-point matrix

| Capability | [`Classes`](Configuration-Classes.md) | [`Library`](The-Library-Object.md) | [`Config`](The-Config-Facade.md) |
| --- | :---: | :---: | :---: |
| `get` / `set` / `has` / `remove` / `all` | ✅ | ✅ | ✅ |
| Dotted-path keys (`db.host`) | ✅ | ✅ | ✅ |
| Case-insensitive keys | ✅ | ✅ | ✅ |
| Initial data source | subclass properties | constructor / loaders | loaders |
| Load array — [`setArray()`](Loaders.md#setarray) | ❌ | ✅ | ✅ |
| Load PHP file — [`setFile()`](Loaders.md#setfile) | ❌ | ✅ | ✅ |
| Load directory — [`setDir()`](Loaders.md#setdir) | ❌ | ✅ | ✅ |
| Load class/object — [`setClass()`](Loaders.md#setclass) | ❌ | ✅ | ✅ |
| Replace whole tree — `replace()` | ❌ | ✅ | ✅ |
| [Object-style access](Object-Access.md) (`$cfg->db->host`) | ❌ | ✅ | ❌ |
| Lifetime | per instance | per instance | process-wide singleton |

## Package metadata

- **License**: MIT
- **Minimum PHP**: 8.2 (tested on 8.2 – 8.6)
- **Runtime dependencies**: [`initphp/parameterbag`](https://packagist.org/packages/initphp/parameterbag) `^3.0`
- **Packagist**: [`initphp/config`](https://packagist.org/packages/initphp/config)
- **Source**: [github.com/InitPHP/Config](https://github.com/InitPHP/Config)
- **Issues**: [github.com/InitPHP/Config/issues](https://github.com/InitPHP/Config/issues)
- **Discussions**: [github.com/orgs/InitPHP/discussions](https://github.com/orgs/InitPHP/discussions)
- **Security**: [`SECURITY.md`](https://github.com/InitPHP/.github/blob/main/SECURITY.md)

If something in this wiki is unclear, ambiguous, or wrong, please
[open an issue](https://github.com/InitPHP/Config/issues/new) —
documentation fixes are reviewed eagerly.
