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

namespace InitPHP\Config\Interfaces;

/**
 * The minimal contract every configuration store in this package
 * fulfils, regardless of where its data originates (a subclass's
 * properties, an imported array, a PHP file, or a directory of files).
 *
 * Keys may use dotted-path notation (e.g. `database.user`) to address a
 * value nested inside the configuration tree.
 */
interface ConfigInterface
{
    /**
     * Assign a value to a configuration key.
     *
     * When the key uses dotted-path notation the intermediate arrays
     * are created on demand.
     *
     * @param string $key   The configuration key (dotted paths allowed).
     * @param mixed  $value The value to store.
     *
     * @return static The same instance, for fluent chaining.
     */
    public function set(string $key, $value): self;

    /**
     * Return the value stored under a configuration key.
     *
     * @param string $key     The configuration key (dotted paths allowed).
     * @param mixed  $default The value returned when the key is absent.
     *
     * @return mixed The stored value, or $default when the key is absent.
     */
    public function get(string $key, $default = null);

    /**
     * Tell whether a configuration key exists.
     *
     * A key whose stored value is `null` is still considered present.
     *
     * @param string $key The configuration key (dotted paths allowed).
     */
    public function has(string $key): bool;

    /**
     * Remove a configuration key.
     *
     * Removing an absent key is a no-op.
     *
     * @param string $key The configuration key (dotted paths allowed).
     *
     * @return static The same instance, for fluent chaining.
     */
    public function remove(string $key): self;

    /**
     * Return the entire configuration tree as a plain array.
     *
     * @return array<array-key, mixed>
     */
    public function all(): array;
}
