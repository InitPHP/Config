# Exceptions

The package throws a single exception type from its loaders:

```
\RuntimeException
└── InitPHP\Config\Exceptions\ConfigException
```

Because it extends the SPL `\RuntimeException`, existing
`catch (\RuntimeException $e)` blocks continue to work. Most applications
will want to catch the more specific type explicitly so the intent is
unambiguous.

```php
use InitPHP\Config\Config;
use InitPHP\Config\Exceptions\ConfigException;

try {
    Config::setFile('db', '/path/to/missing.php');
} catch (ConfigException $e) {
    error_log($e->getMessage());
}
```

## When it is raised

Every failure mode has a dedicated named constructor with a consistent
message.

| Trigger | Named constructor | Message |
| --- | --- | --- |
| [`setFile()`](Loaders.md#setfile) path does not exist | `ConfigException::fileNotFound()` | `Configuration file "…" was not found.` |
| [`setFile()`](Loaders.md#setfile) target lacks a `.php` extension | `ConfigException::notAPhpFile()` | `Configuration file "…" must have a ".php" extension.` |
| [`setFile()`](Loaders.md#setfile) file does not return an array | `ConfigException::fileMustReturnArray()` | `Configuration file "…" must return an array.` |
| [`setDir()`](Loaders.md#setdir) path is not a directory | `ConfigException::notADirectory()` | `"…" is not a valid directory.` |
| [`setDir()`](Loaders.md#setdir) directory cannot be read | `ConfigException::directoryNotReadable()` | `Could not read the directory "…".` |
| [`setClass()`](Loaders.md#setclass) class name does not exist | `ConfigException::classNotFound()` | `Class "…" was not found.` |

### File not found

```php
Config::setFile('db', __DIR__ . '/config/db.php');

// ConfigException:
//   Configuration file "/…/config/db.php" was not found.
```

### File does not return an array

A loaded file must `return` an associative array:

```php
// config/bad.php
echo 'oops'; // no return

Config::setFile('bad', __DIR__ . '/config/bad.php');

// ConfigException:
//   Configuration file "/…/config/bad.php" must return an array.
```

### Wrong extension

```php
Config::setFile('db', __DIR__ . '/config/db.ini');

// ConfigException:
//   Configuration file "/…/config/db.ini" must have a ".php" extension.
```

### Unknown class

```php
Config::setClass('App\\Config\\DoesNotExist');

// ConfigException:
//   Class "App\Config\DoesNotExist" was not found.
```

## Catching it

```php
use InitPHP\Config\Config;
use InitPHP\Config\Exceptions\ConfigException;

try {
    Config::setDir('app', __DIR__ . '/config');
} catch (ConfigException $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
```

## Tolerating an optional file

A missing override file is often expected. Catch and ignore:

```php
try {
    Config::setFile('local', __DIR__ . '/config/local.php');
} catch (ConfigException) {
    // Optional override — safe to skip when absent.
}
```

## Re-throwing from your own boundary

If you wrap the package in a service of your own, re-throw as your own
domain type and preserve the original as `$previous`:

```php
use InitPHP\Config\Exceptions\ConfigException;
use InitPHP\Config\Library;

final class ConfigLoader
{
    public function load(string $dir): Library
    {
        try {
            return (new Library())->setDir('app', $dir);
        } catch (ConfigException $e) {
            throw new \App\BootstrapException(
                'Application config could not be loaded: ' . $e->getMessage(),
                0,
                $e,
            );
        }
    }
}
```

## Things that look like errors but aren't

The read API is deliberately forgiving — these never throw:

| Situation | What happens |
| --- | --- |
| `get('missing')` | Returns `$default` (default: `null`). |
| `remove('missing')` | Silent no-op. |
| [`setArray()`](Loaders.md#setarray) / [`setFile()`](Loaders.md#setfile) with a `null` name | Merges into the root instead of failing. |
| `Config::reset()` with no active instance | Silent no-op. |

```php
Config::get('does.not.exist');             // null
Config::get('does.not.exist', 'fallback'); // 'fallback'
Config::remove('does.not.exist');          // no-op, no exception
```

## Related reading

- [Loaders](Loaders.md) — where each exception originates.
- [API Reference → ConfigException](API-Reference.md#configexception) — the factory list.
