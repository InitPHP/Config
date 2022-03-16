<?php
/**
 * Config.php
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

namespace InitPHP\Config;

/**
 *
 * Intermediate class to use the methods of the \PHPConfig\Library class as static.
 *
 * @see \InitPHP\Config\Library
 *
 * @method static string version()
 * @method static mixed get(string $key = null, mixed $default = null)
 * @method static Library set(string $key, mixed $value)
 * @method static bool has(string $key)
 * @method static array all()
 * @method static Library setArray(null|string $name = null, array $assoc = [])
 * @method static Library setDir(string|null $name, string $path, array $exclude = [])
 * @method static Library setFile(string|null $name, string $path)
 * @method static Library setClass(string|object $classOrObject)
 * @method static void close()
 *
 */
class Config
{

    private static Library $Instance;

    public function __construct()
    {
        self::getInstance();
    }

    public function __destruct()
    {
        self::getInstance()->close();
    }

    public function __debugInfo()
    {
        return self::getInstance()->__debugInfo();
    }

    public function __get($name)
    {
        return self::getInstance()->__get($name);
    }

    public function __call($name, $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::getInstance()->{$name}(...$arguments);
    }

    private static function getInstance(): Library
    {
        if(!isset(self::$Instance)){
            self::$Instance = new Library();
        }
        return self::$Instance;
    }

}
