# Recipes

Practical, copy-pasteable patterns built on top of the public API. Each
recipe is self-contained and links back into the reference material for
the methods it uses.

| Recipe | Solves |
| --- | --- |
| [Environment-Aware Config](Recipe-Environment-Aware-Config.md) | A base config directory with per-environment overrides merged on top. |
| [Database Config](Recipe-Database-Config.md) | Loading database credentials from a PHP file and reading them safely. |
| [Typed Config Class](Recipe-Typed-Config-Class.md) | Strongly-typed, IDE-friendly configuration defined as code. |

If you have built something on top of the package that other users
might benefit from, please
[open an issue](https://github.com/InitPHP/Config/issues/new) with a
sketch — recipe contributions are welcomed.

## Pattern sketches

Short snippets that don't need a full page.

### Fallback chain across several keys

```php
use InitPHP\Config\Interfaces\ConfigInterface;

function configWithFallback(ConfigInterface $config, array $keys, $default = null)
{
    foreach ($keys as $key) {
        if ($config->has($key)) {
            return $config->get($key);
        }
    }

    return $default;
}

$dsn = configWithFallback(
    $config,
    ['database.dsn', 'db.dsn', 'DATABASE_URL'],
    'sqlite::memory:',
);
```

### Snapshot and restore

```php
$snapshot = $config->all();
// ... mutate during a unit of work ...
$config->replace($snapshot); // restore the earlier state
```

### Read a subtree as an array or an object

```php
$db = $config->get('db');     // ['host' => '…', 'user' => '…']
$db = $config->db;            // stdClass: $db->host, $db->user  (Library only)
```

## Related reading

- [Loaders](Loaders.md) — the source loaders these recipes build on.
- [The Library Object](The-Library-Object.md) — the injectable object.
- [The Config Facade](The-Config-Facade.md) — the global static layer.
