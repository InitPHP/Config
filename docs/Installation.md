# Installation

## Requirements

| | |
| --- | --- |
| PHP | `^8.2` (tested on 8.2 – 8.5) |
| Extensions | None |
| Runtime dependencies | [`initphp/parameterbag`](https://packagist.org/packages/initphp/parameterbag) `^3.0` |

`initphp/parameterbag` is pulled in automatically by Composer. Development
dependencies (PHPUnit, PHPStan, PHP-CS-Fixer) live under `require-dev` and
are not installed when you depend on `initphp/config` from another
project.

## Install via Composer

```bash
composer require initphp/config
```

The classes are autoloaded under the `InitPHP\Config` namespace via
PSR-4:

```json
{
    "autoload": {
        "psr-4": {
            "InitPHP\\Config\\": "src/"
        }
    }
}
```

## Verifying the install

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use InitPHP\Config\Library;

$config = new Library(['hello' => 'world']);
echo $config->get('hello'); // world
```

If the autoloader cannot locate the class, double-check that you ran
`composer install` (or `composer dump-autoload`) and that your script
requires `vendor/autoload.php` before instantiating anything.

## Supported PHP versions in CI

Every PR runs the test matrix against:

- PHP 8.2
- PHP 8.3
- PHP 8.4
- PHP 8.5

The pipeline also runs static analysis (PHPStan, level 8) and a coding
standard check (PHP-CS-Fixer). If you discover a regression on a
supported version, please
[file an issue](https://github.com/InitPHP/Config/issues/new) and include
the PHP version reported by `php -v`.

## Next steps

- Walk through the everyday API in [Quick Start](Quick-Start.md).
- Learn how keys are parsed in [Keys & Dotted Paths](Keys-and-Dotted-Paths.md).
- Upgrading from v1? Read the [Migration Guide](Migration-Guide.md).
