<?php

declare(strict_types=1);

namespace InitPHP\Config\Tests\Fixtures;

use InitPHP\Config\Classes;

/**
 * Fixture documenting which property visibilities {@see Classes} imports:
 * public and protected are imported, the subclass's private is not.
 */
final class VisibilityConfig extends Classes
{
    public string $publicValue = 'public';

    protected string $protectedValue = 'protected';

    private string $privateValue = 'private';

    public function getPrivateValue(): string
    {
        return $this->privateValue;
    }
}
