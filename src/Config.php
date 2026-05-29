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

/**
 * Static facade over a shared {@see Library} instance.
 *
 * Every call is forwarded to a lazily created, process-wide
 * {@see Library} singleton, which lets configuration be registered in
 * one place and read anywhere:
 *
 * ```php
 * Config::setArray('site', ['url' => 'http://lvh.me']);
 * Config::get('site.url'); // "http://lvh.me"
 * ```
 *
 * Use {@see self::reset()} to discard the shared instance — chiefly in
 * tests, where each case needs a clean slate.
 *
 * @method static string  version()
 * @method static mixed   get(string $key, $default = null)
 * @method static Library set(string $key, $value)
 * @method static bool    has(string $key)
 * @method static Library remove(string $key)
 * @method static array   all()
 * @method static Library replace(array $data)
 * @method static Library setArray(?string $name, array $assoc = [])
 * @method static Library setFile(?string $name, string $path)
 * @method static Library setDir(?string $name, string $path, array $exclude = [])
 * @method static Library setClass(string|object $classOrObject)
 * @method static void    close()
 *
 * @see Library
 */
final class Config
{
    /**
     * The shared library instance, created on first access.
     */
    private static ?Library $instance = null;

    /**
     * The facade is a pure static utility and must not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Forward every static call to the shared {@see Library} instance.
     *
     * @param array<int, mixed> $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }

    /**
     * Discard the shared instance so the next call starts from a clean,
     * empty configuration tree.
     */
    public static function reset(): void
    {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }

    /**
     * Return the shared instance, creating it on first access.
     */
    private static function getInstance(): Library
    {
        if (self::$instance === null) {
            self::$instance = new Library();
        }

        return self::$instance;
    }
}
