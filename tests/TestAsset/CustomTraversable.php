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

use Iterator;

use function current;
use function key;
use function next;
use function reset;

final class CustomTraversable implements Iterator
{
    /** @var array<int|string, mixed> */
    private array $data;

    /**
     * @param array<int|string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return false|mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @return false|mixed|void
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return key($this->data);
    }

    public function valid(): bool
    {
        return null !== $this->key();
    }

    /**
     * @return false|mixed
     */
    public function rewind()
    {
        return reset($this->data);
    }
}
