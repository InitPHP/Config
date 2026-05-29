# Error Handling

All loader failures throw a single, dedicated exception type:
[`InitPHP\Config\Exceptions\ConfigException`](../src/Exceptions/ConfigException.php).
It extends the SPL `RuntimeException`, so existing
`catch (\RuntimeException)` blocks keep working.

```php
use InitPHP\Config\Config;
use InitPHP\Config\Exceptions\ConfigException;

try {
    Config::setFile('db', '/path/to/missing.php');
} catch (ConfigException $e) {
    error_log($e->getMessage());
}
```

## Failure modes

Each failure has a dedicated named constructor with a consistent message:

| Trigger | Named constructor | Message |
| --- | --- | --- |
| `setFile()` path does not exist | `ConfigException::fileNotFound()` | `Configuration file "…" was not found.` |
| `setFile()` target lacks a `.php` extension | `ConfigException::notAPhpFile()` | `Configuration file "…" must have a ".php" extension.` |
| `setFile()` file does not return an array | `ConfigException::fileMustReturnArray()` | `Configuration file "…" must return an array.` |
| `setDir()` path is not a directory | `ConfigException::notADirectory()` | `"…" is not a valid directory.` |
| `setDir()` directory cannot be read | `ConfigException::directoryNotReadable()` | `Could not read the directory "…".` |
| `setClass()` class name does not exist | `ConfigException::classNotFound()` | `Class "…" was not found.` |

## What does *not* throw

These are intentionally lenient and never raise:

- **Reading a missing key** — `get()` returns the supplied `$default`
  (`null` by default).
- **Removing a missing key** — `remove()` is a no-op.
- **`setArray()` / `setFile()` with a `null` name** — merges at the root
  rather than failing.

```php
Config::get('does.not.exist');          // null
Config::get('does.not.exist', 'fallback'); // "fallback"
Config::remove('does.not.exist');        // no-op, no exception
```

## Example: tolerating an optional file

```php
use InitPHP\Config\Config;
use InitPHP\Config\Exceptions\ConfigException;

try {
    Config::setFile('local', __DIR__ . '/config/local.php');
} catch (ConfigException) {
    // Optional override file — safe to ignore when absent.
}
```
