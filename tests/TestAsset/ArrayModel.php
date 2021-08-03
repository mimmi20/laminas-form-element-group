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

namespace Mimmi20Test\Form\Element\Group\TestAsset;

final class ArrayModel
{
    use ModelTrait;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }
}
