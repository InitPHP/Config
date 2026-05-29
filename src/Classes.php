<?php

/**
 * This file is part of the initphp/config package.
 *
 * (c) Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @link https://github.com/InitPHP/Config
 */

declare(strict_types=1);

namespace InitPHP\Config;

use function get_class_vars;

/**
 * Base class that exposes a subclass's public properties as
 * configuration values.
 *
 * Extend it, declare your configuration as public properties (scalars
 * or nested arrays), and read them back through the {@see ConfigInterface}
 * API:
 *
 * ```php
 * final class AppConfig extends \InitPHP\Config\Classes
 * {
 *     public string $url = 'http://lvh.me';
 *     public array  $db  = ['host' => 'localhost', 'user' => 'root'];
 * }
 *
 * $config = new AppConfig();
 * $config->get('url');     // "http://lvh.me"
 * $config->get('db.host'); // "localhost"
 * ```
 *
 * Only the property *default* values declared on the class are imported,
 * and only those visible from this base class in the inheritance chain —
 * i.e. public and protected properties, but not a subclass's private
 * ones. The infrastructure property inherited from {@see AbstractConfig}
 * is excluded so it never leaks into the configuration tree.
 */
abstract class Classes extends AbstractConfig
{
    public function __construct()
    {
        /** @var array<string, mixed> $data */
        $data = get_class_vars(static::class);
        unset($data['parameterBag']);

        $this->parameterBag = self::newParameterBag($data);
    }
}
