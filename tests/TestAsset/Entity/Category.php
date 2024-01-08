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

namespace Mimmi20Test\Form\Element\Group\TestAsset\Entity;

final class Category
{
    private int $id      = 0;
    private string $name = '';

    /**
     * @return $this
     *
     * @throws void
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /** @throws void */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     *
     * @throws void
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /** @throws void */
    public function getId(): int
    {
        return $this->id;
    }
}
