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

use InitPHP\Config\Exceptions\ConfigException;
use stdClass;

use function basename;
use function class_exists;
use function end;
use function explode;
use function get_class;
use function get_class_vars;
use function get_object_vars;
use function glob;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function is_object;
use function rtrim;
use function strtolower;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * The package's main entry point.
 *
 * A {@see Library} instance is a configuration tree that can be
 * populated from several sources — plain arrays, PHP files that return
 * arrays, whole directories of such files, and the public properties of
 * a class or object — and queried through the {@see ConfigInterface} API
 * with dotted-path, case-insensitive keys.
 *
 * Stored values can also be read as nested objects through property
 * access:
 *
 * ```php
 * $library = new Library();
 * $library->set('db.host', 'localhost');
 * $library->db->host; // "localhost"
 * ```
 */
final class Library extends AbstractConfig
{
    /**
     * The current package version.
     */
    public const VERSION = '2.0.0';

    /**
     * @param array<array-key, mixed> $data Initial configuration tree.
     */
    public function __construct(array $data = [])
    {
        $this->parameterBag = self::newParameterBag($data);
    }

    /**
     * Expose a top-level configuration entry as a value or a nested
     * {@see stdClass}.
     *
     * Scalar entries are returned as-is; array entries are converted to
     * a (recursively built) {@see stdClass}. Unknown keys return `null`.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $value = $this->parameterBag->get($name);
        if ($value === null) {
            return null;
        }

        return is_array($value) ? $this->arrayToObject($value) : $value;
    }

    /**
     * @return array{version: string, data: array<array-key, mixed>}
     */
    public function __debugInfo(): array
    {
        return [
            'version' => self::VERSION,
            'data'    => $this->all(),
        ];
    }

    /**
     * Return the current package version.
     */
    public function version(): string
    {
        return self::VERSION;
    }

    /**
     * Replace the entire configuration tree with a new array.
     *
     * Unlike {@see self::set()} this discards every existing entry.
     *
     * @param array<array-key, mixed> $data The new configuration tree.
     *
     * @return $this
     */
    public function replace(array $data): self
    {
        $this->parameterBag->replace($data);

        return $this;
    }

    /**
     * Import an associative array.
     *
     * When $name is `null` or an empty string the array is merged into
     * the root of the configuration tree; otherwise it is stored under
     * $name (which may itself be a dotted path).
     *
     * @param string|null             $name  Parent key, or `null`/`''` for the root.
     * @param array<array-key, mixed> $assoc The array to import.
     *
     * @return $this
     */
    public function setArray(?string $name, array $assoc = []): self
    {
        if ($name === null || $name === '') {
            $this->parameterBag->merge($assoc);

            return $this;
        }
        $this->parameterBag->set($name, $assoc);

        return $this;
    }

    /**
     * Import a PHP file that returns an associative array.
     *
     * @param string|null $name Parent key, or `null`/`''` for the root.
     * @param string      $path Full path to a PHP file returning an array.
     *
     * @throws ConfigException If the file does not exist, is not a PHP
     *                         file, or does not return an array.
     *
     * @return $this
     */
    public function setFile(?string $name, string $path): self
    {
        if (!is_file($path)) {
            throw ConfigException::fileNotFound($path);
        }
        $data = $this->requirePhp($path);
        if (!is_array($data)) {
            throw ConfigException::fileMustReturnArray($path);
        }

        return $this->setArray($name, $data);
    }

    /**
     * Import every top-level PHP file in a directory as configuration.
     *
     * Each file is loaded under a key derived from its base name (e.g.
     * `db.php` becomes `db`). When $name is given it is used as a common
     * prefix so the files land under `name.<file>`.
     *
     * @param string|null               $name    Common parent key, or `null`/`''` for none.
     * @param string                    $path    Directory holding the PHP files.
     * @param array<int|string, string> $exclude Base names (with or without the
     *                                           `.php` suffix) to skip.
     *
     * @throws ConfigException If the path is not a readable directory or
     *                         a loaded file does not return an array.
     *
     * @return $this
     */
    public function setDir(?string $name, string $path, array $exclude = []): self
    {
        if (!is_dir($path)) {
            throw ConfigException::notADirectory($path);
        }
        $pattern = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . '*.php';
        $files = glob($pattern);
        if ($files === false) {
            throw ConfigException::directoryNotReadable($path);
        }
        $prefix = ($name === null || $name === '') ? '' : $name . '.';

        $excluded = [];
        foreach ($exclude as $row) {
            $excluded[] = strtolower(basename($row, '.php'));
        }

        foreach ($files as $file) {
            $key = strtolower(basename($file, '.php'));
            if (in_array($key, $excluded, true)) {
                continue;
            }
            $this->setFile($prefix . $key, $file);
        }

        return $this;
    }

    /**
     * Import the public properties of a class or object.
     *
     * For a class name the property *defaults* are imported; for an
     * object the *current* public property values are imported. In both
     * cases the entry is stored under the class's short name (its
     * namespace stripped).
     *
     * @param string|object $classOrObject Fully-qualified class name or an instance.
     *
     * @throws ConfigException If a class name is given but no such class exists.
     *
     * @return $this
     */
    public function setClass($classOrObject): self
    {
        if (is_object($classOrObject)) {
            $class = get_class($classOrObject);
            $properties = get_object_vars($classOrObject);
        } elseif (class_exists($classOrObject)) {
            $class = $classOrObject;
            $properties = get_class_vars($classOrObject);
        } else {
            throw ConfigException::classNotFound((string) $classOrObject);
        }

        $segments = explode('\\', $class);
        $name = (string) end($segments);

        return $this->setArray($name, $properties);
    }

    /**
     * Recursively convert an array into a nested {@see stdClass}.
     *
     * @param array<array-key, mixed> $data
     */
    private function arrayToObject(array $data): stdClass
    {
        $object = new stdClass();
        foreach ($data as $key => $value) {
            $object->{$key} = is_array($value) ? $this->arrayToObject($value) : $value;
        }

        return $object;
    }

    /**
     * Include a PHP file and return whatever it yields.
     *
     * @throws ConfigException If $path does not have a `.php` extension.
     *
     * @return mixed The file's return value.
     */
    private function requirePhp(string $path)
    {
        if (substr($path, -4) !== '.php') {
            throw ConfigException::notAPhpFile($path);
        }

        return require $path;
    }
}
