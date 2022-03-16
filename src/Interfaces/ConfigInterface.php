<?php
/**
 * ConfigInterface.php
 *
 * This file is part of InitPHP.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    http://initphp.github.io/license.txt  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

declare(strict_types=1);

namespace InitPHP\Config\Interfaces;

interface ConfigInterface
{
    /**
     * Sets the value of the specified configuration.
     *
     * @param string $key Configuration key.
     * @param mixed $value The new value of the configuration.
     * @return ConfigInterface
     */
    public function set(string $key, $value): ConfigInterface;

    /**
     * Returns the value of the specified configuration. If the configuration exists, $default_value is returned.
     *
     * @param string $key The key to the desired configuration. If `NULL` it returns the entire configuration array.
     * @param mixed $default The data to return if the desired configuration is not found.
     * @return mixed The value of the configuration or `$default`
     */
    public function get(string $key, $default = null);

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @return ConfigInterface
     */
    public function remove(string $key): ConfigInterface;

    /**
     * @return array
     */
    public function all(): array;

}
