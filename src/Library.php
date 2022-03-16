<?php
/**
 * Library.php
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

use \Exception;
use \InitPHP\ParameterBag\ParameterBag;

use const DIRECTORY_SEPARATOR;

use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function glob;
use function rtrim;
use function basename;
use function strtolower;
use function get_class;
use function class_exists;
use function get_class_vars;
use function end;
use function explode;
use function substr;

final class Library
{

    public const VERSION = '1.0';

    protected ParameterBag $_ParameterBag;

    public function __construct(array $data = [])
    {
        $this->_ParameterBag = new ParameterBag($data, [
            'isMulti'   => true,
        ]);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function __get($name)
    {
        if(($value = $this->_ParameterBag->get($name, null)) === null){
            return null;
        }
        $obj = $this->convertObj($name, $value);
        return $obj->{$name};
    }

    public function __debugInfo()
    {
        return [
            'version'   => self::VERSION,
            'data'      => $this->all(),
        ];
    }

    public function close(): void
    {
        if(isset($this->_ParameterBag)){
            $this->_ParameterBag->close();
            unset($this->_ParameterBag);
        }
    }

    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * Sets the value of the specified configuration.
     *
     * @param string|null $key
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function set(?string $key, $value): self
    {
        if($key !== null){
            $this->_ParameterBag->set($key, $value);
            return $this;
        }
        if(!is_array($value)){
            throw new Exception('The value must be an array to set the entire configuration array.');
        }
        if(isset($this->_ParameterBag)){
            $this->_ParameterBag->close();
        }
        $this->_ParameterBag = new ParameterBag($value, [
            'isMulti'   => true,
        ]);

        return $this;
    }

    /**
     * Returns the value of the requested configuration data.
     *
     * @param string $key <p>The key of the requested configuration data.</p>
     * @param mixed $default <p>The value to return if the requested data is not found.</p>
     * @return array|mixed|string|null
     */
    public function get(string $key, $default = null)
    {
        return $this->_ParameterBag->get($key, $default);
    }

    /**
     * Queries whether the desired configuration value exists.
     *
     * @param string $key <p>The key of the configuration value to query.</p>
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->_ParameterBag->has($key);
    }

    /**
     * Returns the entire configuration array.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->_ParameterBag->all();
    }

    /**
     * Imports an array.
     *
     * @param string|null $name <p>The parent array key of the configurations to load. If `null` is loaded directly. It is possible to install in a subdirectory by specifying a name.</p>
     * @param array $assoc <p>The array to import.</p>
     * @return $this
     * @throws Exception
     */
    public function setArray(?string $name, array $assoc = []): self
    {
        return $this->set($name, $assoc);
    }

    /**
     * Loads PHP files in a directory as configuration files.
     *
     * @param string|null $name <p>The parent configuration name to use for the directory.</p>
     * @param string $path <p>Array holding configuration files.</p>
     * @param array $exclude <p>Array declaring files to be excluded.</p>
     * @return $this
     * @throws Exception
     */
    public function setDir(?string $name, string $path, array $exclude = []): self
    {
        if(!is_dir($path)){
            throw new Exception('"' . $path . '" is not a valid directory.');
        }
        $pattern = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . '*.php';
        if(($files = glob($pattern)) === FALSE){
            throw new Exception('Could not read directory "' . $path . '".');
        }
        $prefix = empty($name) ? $name . '.' : '';

        $exc = [];
        foreach ($exclude as $row) {
            $exc[] = strtolower(basename($row, '.php'));
        }
        $hasExclude = !empty($exc);
        unset($exclude);

        foreach ($files as $file) {
            $basename = strtolower(basename($file, '.php'));
            if($hasExclude && in_array($basename, $exc) !== FALSE){
                continue;
            }
            $this->setFile(($prefix . $basename), $file);
        }
        return $this;
    }

    /**
     * Loads configs from PHP file that returns associative array.
     *
     * @uses \InitPHP\Config\Library::setDir()
     *
     * @param string|null $name
     * @param string $path <p>The full path to the ".php" file that returns an associative array.</p>
     * @return $this
     * @throws Exception
     */
    public function setFile(?string $name, string $path): self
    {
        if(!is_file($path)){
            throw new Exception('"' . $path . '" file not found.');
        }
        $data = $this->require_php($path);
        if(!is_array($data)){
            throw new Exception('The "' . $path . '" file should return an array.');
        }
        if($name !== null){
            $name = strtolower($name);
        }
        return $this->set($name, $data);
    }

    /**
     * Imports the public properties of the specified class or object.
     *
     * @param string|object $classOrObject <p>The full name of the class or the object created with the class.</p>
     * @return $this
     * @throws Exception
     */
    public function setClass($classOrObject): self
    {
        if(is_object($classOrObject)){
            $class = get_class($classOrObject);
        }elseif(is_string($classOrObject) && class_exists($classOrObject)){
            $class = $classOrObject;
        }else{
            throw new Exception('Class "' . (string)$classOrObject . '" not found.');
        }
        $properties = get_class_vars($class);
        $split = explode('\\', $class);
        $name = end($split);
        return $this->set($name, $properties);
    }

    /**
     * Converts an array to object and returns
     *
     * @uses \InitPHP\Config\Library::__get()
     *
     * @param string $key
     * @param mixed $values
     * @return object
     */
    private function convertObj(string $key, $values): object
    {
        $obj = new \stdClass();
        if(!is_array($values)){
            $obj->{$key} = $values;
            return $obj;
        }
        foreach ($values as $name => $value) {
            if(is_array($value)){
                $value = $this->convertObj($name, $value);
            }
            $obj->{$key} = $value;
        }
        return $obj;
    }

    /**
     * @param string $path
     * @return array
     * @throws Exception
     */
    private function require_php(string $path)
    {
        if(substr($path, -4) !== '.php'){
            throw new Exception('"' . $path . '" must be a PHP file.');
        }
        return require $path;
    }

}
