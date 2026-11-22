# Recipe: Typed Config Class

**Goal:** define application configuration as a typed PHP class so you
get IDE autocompletion, static-analysis coverage, and a single source of
truth — while still reading it through the uniform
[`ConfigInterface`](API-Reference.md#configinterface) API.

## Define the class

Extend [`Classes`](Configuration-Classes.md) and declare your settings as
public properties with defaults:

```php
namespace App\Config;

use InitPHP\Config\Classes;

final class AppConfig extends Classes
{
    public string $name     = 'InitPHP App';
    public string $env       = 'production';
    public bool   $debug     = false;
    public string $timezone  = 'UTC';

    /** @var array<string, mixed> */
    public array $db = [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'app',
        'charset' => 'utf8mb4',
    ];

    /** @var array<string, mixed> */
    public array $cache = [
        'driver' => 'file',
        'ttl'    => 3600,
    ];
}
```

## Read it

```php
use App\Config\AppConfig;

$config = new AppConfig();

$config->get('name');         // 'InitPHP App'
$config->get('db.host');      // '127.0.0.1'
$config->get('db.port');      // 3306
$config->get('cache.driver'); // 'file'

$config->has('db.charset');   // true
$config->get('db.ssl', false); // false (default — key absent)
```

Nested array properties become dotted paths automatically; see
[Keys & Dotted Paths](Keys-and-Dotted-Paths.md).

## Override at runtime

Because a config class shares the full read/write surface, you can adjust
values after construction — handy for tests or per-request tweaks:

```php
$config->set('debug', true);
$config->set('db.host', 'db.internal');

$config->get('debug');   // true
$config->get('db.host'); // 'db.internal'
```

## Inject it

Type-hint against the interface so consumers stay decoupled and testable:

```php
use InitPHP\Config\Interfaces\ConfigInterface;
use App\Config\AppConfig;

$container->set(ConfigInterface::class, fn () => new AppConfig());

final class Mailer
{
    public function __construct(private ConfigInterface $config) {}

    public function fromAddress(): string
    {
        return $this->config->get('mail.from', 'no-reply@example.com');
    }
}
```

In a test, pass any `ConfigInterface` — including a plain
[`Library`](The-Library-Object.md) seeded with just the keys under test:

```php
use InitPHP\Config\Library;

$mailer = new Mailer(new Library(['mail' => ['from' => 'test@example.com']]));
$mailer->fromAddress(); // 'test@example.com'
```

## Visibility rules

`Classes` imports **public and protected** property defaults (not
private). Use a protected property for a value you want available as
config but not as part of the public class surface:

```php
final class AppConfig extends Classes
{
    public string    $name   = 'InitPHP App';
    protected string $secret = 'shared-internally'; // imported as 'secret'
    private string   $token  = 'never-imported';    // NOT imported
}
```

See [Configuration Classes → What gets imported](Configuration-Classes.md#what-gets-imported).

## When to prefer this over file loaders

| Typed class | File / directory loaders |
| --- | --- |
| Config known at author time | Config supplied by ops / env |
| Autocompletion + static analysis | Dynamic, discovered at runtime |
| One class, version-controlled | Many files, environment overrides |

The two compose well: define stable defaults as a class, and layer
runtime overrides with a [`Library`](The-Library-Object.md) when needed.

## Related reading

- [Configuration Classes](Configuration-Classes.md)
- [The Library Object](The-Library-Object.md) — for runtime/injected config.
- [Environment-Aware Config](Recipe-Environment-Aware-Config.md) — file-based overrides.
