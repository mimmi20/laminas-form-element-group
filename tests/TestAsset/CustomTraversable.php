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

use Iterator;

use function current;
use function key;
use function next;
use function reset;

final class CustomTraversable implements Iterator
{
    /**
     * @param array<int|string, mixed> $data
     *
     * @throws void
     */
    public function __construct(private array $data)
    {
        // nothing to do
    }

    /** @throws void */
    public function current(): mixed
    {
        return current($this->data);
    }

    /** @throws void */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return int|string|null
     *
     * @throws void
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /** @throws void */
    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /** @throws void */
    public function rewind(): void
    {
        reset($this->data);
    }
}
