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

use InitPHP\Config\Interfaces\ConfigInterface;
use InitPHP\ParameterBag\ParameterBag;

/**
 * Shared implementation of {@see ConfigInterface}.
 *
 * The configuration tree is stored in an {@see ParameterBag} configured
 * for dotted-path (`isMulti`), case-insensitive access. Concrete
 * subclasses are responsible for populating {@see self::$parameterBag}
 * in their constructor.
 *
 * @see Library  Loads configuration from arrays, files and classes.
 * @see Classes  Treats a subclass's own properties as configuration.
 */
abstract class AbstractConfig implements ConfigInterface
{
    /**
     * The underlying parameter store. Subclasses must initialise this
     * before any of the CRUD methods below are called, preferably via
     * {@see self::newParameterBag()} so the dotted-path and
     * case-insensitive options stay consistent across the package.
     */
    protected ParameterBag $parameterBag;

    /**
     * Build a parameter store configured the way this package expects:
     * dotted-path access enabled and keys compared case-insensitively.
     *
     * @param array<array-key, mixed> $data Initial configuration tree.
     */
    final protected static function newParameterBag(array $data = []): ParameterBag
    {
        return new ParameterBag($data, [
            'isMulti'         => true,
            'caseInsensitive' => true,
        ]);
    }

    /**
     * Release the underlying parameter store when the object is
     * destroyed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): ConfigInterface
    {
        $this->parameterBag->set($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = null)
    {
        return $this->parameterBag->get($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->parameterBag->has($key);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): ConfigInterface
    {
        $this->parameterBag->remove($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->parameterBag->all();
    }

    /**
     * Clear the configuration tree.
     *
     * The instance remains usable: the underlying store is reset to an
     * empty, dotted-path, case-insensitive bag so that subsequent
     * {@see self::set()} calls keep working as expected.
     */
    public function close(): void
    {
        if (isset($this->parameterBag)) {
            $this->parameterBag = self::newParameterBag();
        }
    }
}
