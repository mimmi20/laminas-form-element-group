<?php
/**
 * This file is part of the mimmi20/laminas-form-element-group package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Form\Element\TestAsset;

use DomainException;

use function property_exists;

trait ModelTrait
{
    /** @var mixed */
    private $foo;

    /** @var mixed */
    private $bar;

    /** @var mixed */
    private $foobar;

    /**
     * @param mixed $value
     *
     * @throws DomainException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function __set(string $name, $value): void
    {
        throw new DomainException('Overloading to set values is not allowed');
    }

    /**
     * @return mixed
     *
     * @throws DomainException
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new DomainException('Unknown attribute');
    }

    /**
     * @param array<string, mixed> $array
     */
    public function exchangeArray(array $array): void
    {
        foreach ($array as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getArrayCopy(): array
    {
        return [
            'foo' => $this->foo,
            'bar' => $this->bar,
            'foobar' => $this->foobar,
        ];
    }
}
