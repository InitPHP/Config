<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests\Fixtures;

use InitPHP\Config\Classes;

/**
 * Fixture for {@see \InitPHP\Config\Classes} behaviour: a configuration
 * class declared as public property defaults, including a nested array.
 */
final class SampleAppConfig extends Classes
{
    public string $url = 'http://lvh.me';

    public string $name = 'LocalHost';

    /** @var array<string, string> */
    public array $db = [
        'host' => 'localhost',
        'user' => 'root',
    ];
}
