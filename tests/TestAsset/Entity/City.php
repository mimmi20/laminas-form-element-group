<?php
/**
 * This file is part of the mimmi20/laminas-form-element-group package.
 *
 * Copyright (c) 2021-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Form\Element\Group\TestAsset\Entity;

final class City
{
    private string $name;
    private string $zipCode;
    private Country $country;

    /**
     * @return $this
     *
     * @throws void
     */
    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    /** @throws void */
    public function getCountry(): Country
    {
        return $this->country;
    }

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
    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /** @throws void */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }
}
