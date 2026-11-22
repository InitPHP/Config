# API Reference

The full surface of the package's public types. Behavioural deep-dives
are linked into the relevant guides.

Namespace: `InitPHP\Config` (interfaces under `InitPHP\Config\Interfaces`,
exceptions under `InitPHP\Config\Exceptions`).

| Type | Kind | Summary |
| --- | --- | --- |
| [`ConfigInterface`](#configinterface) | interface | The shared read/write contract. |
| [`AbstractConfig`](#abstractconfig) | abstract class | Shared implementation of the contract. |
| [`Classes`](#classes) | abstract class | Properties-as-configuration base. |
| [`Library`](#library) | final class | Main object + loader API. |
| [`Config`](#config) | final class | Static facade over a shared `Library`. |
| [`ConfigException`](#configexception) | class | Thrown by loaders on failure. |

---

## `ConfigInterface`

`InitPHP\Config\Interfaces\ConfigInterface` — the minimal contract every
configuration store fulfils. Depend on this in service signatures.

```php
interface ConfigInterface
{
    public function set(string $key, $value): self;
    public function get(string $key, $default = null);
    public function has(string $key): bool;
    public function remove(string $key): self;
    public function all(): array;
}
```

| Method | Returns | Description |
| --- | --- | --- |
| `set(string $key, $value)` | `static` | Write a value; intermediate arrays are created for dotted keys. |
| `get(string $key, $default = null)` | `mixed` | Read a value, or `$default` when the key is absent. |
| `has(string $key)` | `bool` | Whether the key exists (a stored `null` still counts as present). |
| `remove(string $key)` | `static` | Remove a key; a no-op when it is absent. |
| `all()` | `array` | The entire configuration tree as a plain array. |

Keys may use dotted-path notation; see
[Keys & Dotted Paths](Keys-and-Dotted-Paths.md).

---

## `AbstractConfig`

`InitPHP\Config\AbstractConfig` — `abstract`, `implements ConfigInterface`.
Holds the underlying [`ParameterBag`](https://github.com/InitPHP/ParameterBag)
and implements the shared CRUD + lifecycle. Concrete subclasses populate
the store in their constructor. You do not use this type directly; it is
the common base for [`Classes`](#classes) and [`Library`](#library).

```php
abstract class AbstractConfig implements ConfigInterface
{
    protected ParameterBag $parameterBag;

    final protected static function newParameterBag(array $data = []): ParameterBag;

    public function __destruct();

    public function set(string $key, $value): ConfigInterface;
    public function get(string $key, $default = null);
    public function has(string $key): bool;
    public function remove(string $key): ConfigInterface;
    public function all(): array;

    public function close(): void;
}
```

| Member | Description |
| --- | --- |
| `newParameterBag(array $data = [])` | Builds a store in multi (dotted) + case-insensitive mode. Used by every subclass for consistency. |
| `close()` | Clears the tree by resetting to a fresh empty store; the instance stays usable (options preserved). |
| `__destruct()` | Calls `close()`. |

---

## `Classes`

`InitPHP\Config\Classes` — `abstract`, extends [`AbstractConfig`](#abstractconfig).
Turns a subclass's own properties into a configuration tree. See
[Configuration Classes](Configuration-Classes.md).

```php
abstract class Classes extends AbstractConfig
{
    public function __construct();
}
```

- Imports the **default values** of the subclass's **public and
  protected** properties (private properties are excluded).
- The internal `parameterBag` property is never imported.
- Provides the full [`ConfigInterface`](#configinterface) surface
  inherited from `AbstractConfig`.

```php
final class AppConfig extends Classes
{
    public string $url = 'http://lvh.me';
    public array  $db  = ['host' => 'localhost'];
}

$config = new AppConfig();
$config->get('db.host'); // 'localhost'
```

---

## `Library`

`InitPHP\Config\Library` — `final`, extends [`AbstractConfig`](#abstractconfig).
The main entry point: the CRUD surface plus loaders and object access.
See [The Library Object](The-Library-Object.md).

```php
final class Library extends AbstractConfig
{
    public const VERSION = '2.0.0';

    public function __construct(array $data = []);

    public function __get(string $name);          // object-style read
    public function __debugInfo(): array;

    public function version(): string;
    public function replace(array $data): self;

    public function setArray(?string $name, array $assoc = []): self;
    public function setFile(?string $name, string $path): self;
    public function setDir(?string $name, string $path, array $exclude = []): self;
    public function setClass($classOrObject): self; // string|object
}
```

### Constructor

```php
public function __construct(array $data = []);
```

Seeds the store with `$data` in dotted-path, case-insensitive mode.

### `version`

```php
public function version(): string;
```

Returns the package version string (the `VERSION` constant, e.g.
`'2.0.0'`).

### `replace`

```php
public function replace(array $data): self;
```

Discards the entire tree and installs `$data`. Contrast with
[`set()`](#configinterface), which writes a single key.

### `setArray`

```php
public function setArray(?string $name, array $assoc = []): self;
```

Imports an associative array under `$name`, or merges it into the root
when `$name` is `null`/`''`. See [Loaders → setArray](Loaders.md#setarray).

### `setFile`

```php
public function setFile(?string $name, string $path): self;
```

Loads a PHP file that returns an array. Throws
[`ConfigException`](#configexception) when the file is missing, lacks a
`.php` extension, or does not return an array. See
[Loaders → setFile](Loaders.md#setfile).

### `setDir`

```php
public function setDir(?string $name, string $path, array $exclude = []): self;
```

Loads every top-level `*.php` file in `$path`. `$name` is an optional
common prefix; `$exclude` lists base names to skip. Throws
[`ConfigException`](#configexception) when `$path` is not a directory.
See [Loaders → setDir](Loaders.md#setdir).

### `setClass`

```php
public function setClass($classOrObject): self; // accepts string|object
```

Imports the **public** properties of a class name (defaults) or object
(runtime values) under the class's short name. Throws
[`ConfigException`](#configexception) when a class name does not exist.
See [Loaders → setClass](Loaders.md#setclass).

### `__get`

```php
public function __get(string $name);
```

Reads a top-level entry as a value (scalar) or a recursively built
`stdClass` (array); returns `null` for unknown keys. See
[Object Access](Object-Access.md).

### `__debugInfo`

```php
public function __debugInfo(): array;
```

Returns `['version' => …, 'data' => …]` so `var_dump()` shows the version
and the current tree rather than internals.

---

## `Config`

`InitPHP\Config\Config` — `final`, static facade over a shared
[`Library`](#library). Cannot be instantiated (private constructor). See
[The Config Facade](The-Config-Facade.md).

```php
final class Config
{
    public static function __callStatic(string $name, array $arguments);
    public static function reset(): void;
}
```

| Member | Description |
| --- | --- |
| `__callStatic()` | Forwards any call to the shared `Library` instance. |
| `reset()` | Discards the shared instance (a no-op if none exists). |

The forwarded methods are declared as `@method` annotations for IDE
support:

```php
@method static string  version()
@method static mixed   get(string $key, $default = null)
@method static Library set(string $key, $value)
@method static bool    has(string $key)
@method static Library remove(string $key)
@method static array   all()
@method static Library replace(array $data)
@method static Library setArray(?string $name, array $assoc = [])
@method static Library setFile(?string $name, string $path)
@method static Library setDir(?string $name, string $path, array $exclude = [])
@method static Library setClass(string|object $classOrObject)
@method static void    close()
```

---

## `ConfigException`

`InitPHP\Config\Exceptions\ConfigException` — extends
`\RuntimeException`. Thrown by the loaders. Each failure mode has a named
constructor. See [Exceptions](Exceptions.md).

```php
class ConfigException extends \RuntimeException
{
    public static function fileNotFound(string $path): self;
    public static function fileMustReturnArray(string $path): self;
    public static function notAPhpFile(string $path): self;
    public static function notADirectory(string $path): self;
    public static function directoryNotReadable(string $path): self;
    public static function classNotFound(string $class): self;
}
```

---

## See also

- [Quick Start](Quick-Start.md) — the everyday API in five minutes.
- [Loaders](Loaders.md) — every loader, with examples and error cases.
- [Exceptions](Exceptions.md) — messages and handling patterns.
