<?php

/**
 * This file is part of the mimmi20/laminas-form-element-group package.
 *
 * Copyright (c) 2021-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Form\Element\Group\TestAsset;

use DomainException;

use function property_exists;

final class ArrayModel
{
    use ModelTrait;

    /**
     * @return array<string, mixed>
     *
     * @throws void
     *
     * @api
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @param array<string, mixed> $array
     *
     * @throws DomainException
     *
     * @api
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
     *
     * @throws void
     *
     * @api
     */
    public function getArrayCopy(): array
    {
        return [
            'bar' => $this->bar,
            'foo' => $this->foo,
            'foobar' => $this->foobar,
        ];
    }
}
