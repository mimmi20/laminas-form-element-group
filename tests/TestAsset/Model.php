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
use Laminas\Stdlib\ArraySerializableInterface;
use Override;

use function property_exists;

final class Model implements ArraySerializableInterface
{
    use ModelTrait;

    /**
     * @param array<string, mixed> $array
     *
     * @throws DomainException
     */
    #[Override]
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
     */
    #[Override]
    public function getArrayCopy(): array
    {
        return [
            'bar' => $this->bar,
            'foo' => $this->foo,
            'foobar' => $this->foobar,
        ];
    }
}
