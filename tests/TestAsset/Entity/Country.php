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

final class Country
{
    private string $name      = '';
    private string $continent = '';

    /**
     * @throws void
     *
     * @api
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function setContinent(string $continent): self
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getContinent(): string
    {
        return $this->continent;
    }
}
