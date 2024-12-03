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

use Laminas\Stdlib\ArrayObject;

/** @extends ArrayObject<int|string, mixed> */
final class CustomCollection extends ArrayObject
{
    /**
     * @return array<int|string, array<int|string, mixed>>
     *
     * @throws void
     *
     * @api
     */
    public function toArray(): array
    {
        $ret = [];

        foreach ($this as $key => $obj) {
            $ret[$key] = $obj->toArray();
        }

        return $ret;
    }
}
