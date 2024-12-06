<?php

/**
 * This file is part of the mimmi20/laminas-form-element-group package.
 *
 * Copyright (c) 2021-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Form\Element\Group\TestAsset;

use DomainException;

use function property_exists;

trait ModelTrait
{
    private mixed $foo;
    private mixed $bar;
    private mixed $foobar;

    /**
     * @throws DomainException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __set(string $name, mixed $value): void
    {
        throw new DomainException('Overloading to set values is not allowed');
    }

    /** @throws DomainException */
    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new DomainException('Unknown attribute');
    }
}
