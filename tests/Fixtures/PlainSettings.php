<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests\Fixtures;

/**
 * Plain class (not a {@see \InitPHP\Config\Classes} subclass) used to
 * exercise {@see \InitPHP\Config\Library::setClass()} with both a class
 * name and an instance.
 */
final class PlainSettings
{
    public string $env = 'production';

    /** @var array<string, int> */
    public array $limits = ['rate' => 60];

    protected string $secret = 'hidden';
}
