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

namespace InitPHP\Config\Exceptions;

use RuntimeException;

use function sprintf;

/**
 * Raised when configuration cannot be loaded from the requested source.
 *
 * Every failure mode has a dedicated named constructor so that call
 * sites stay readable and messages remain consistent. Extends the SPL
 * {@see RuntimeException}, so existing `catch (\RuntimeException)` blocks
 * keep working.
 */
class ConfigException extends RuntimeException
{
    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('Configuration file "%s" was not found.', $path));
    }

    public static function fileMustReturnArray(string $path): self
    {
        return new self(sprintf('Configuration file "%s" must return an array.', $path));
    }

    public static function notAPhpFile(string $path): self
    {
        return new self(sprintf('Configuration file "%s" must have a ".php" extension.', $path));
    }

    public static function notADirectory(string $path): self
    {
        return new self(sprintf('"%s" is not a valid directory.', $path));
    }

    public static function directoryNotReadable(string $path): self
    {
        return new self(sprintf('Could not read the directory "%s".', $path));
    }

    public static function classNotFound(string $class): self
    {
        return new self(sprintf('Class "%s" was not found.', $class));
    }
}
