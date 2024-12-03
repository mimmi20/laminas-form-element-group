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

final class Address
{
    private string $street;
    private City | null $city = null;

    /** @var array<int|string, Phone> */
    private array $phones = [];

    /**
     * @throws void
     *
     * @api
     */
    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function setCity(City $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getCity(): City | null
    {
        return $this->city;
    }

    /**
     * @param array<int|string, Phone> $phones
     *
     * @throws void
     *
     * @api
     */
    public function setPhones(array $phones): self
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * @return array<int|string, Phone>
     *
     * @throws void
     *
     * @api
     */
    public function getPhones(): array
    {
        return $this->phones;
    }
}
