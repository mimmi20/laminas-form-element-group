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

namespace Mimmi20Test\Form\Element\Group\TestAsset\Entity;

final class Address
{
    private string $street;

    private ?City $city = null;

    /** @var array<int|string, Phone> */
    private array $phones = [];

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setCity(City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    /**
     * @param array<int|string, Phone> $phones
     */
    public function setPhones(array $phones): self
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * @return array<int|string, Phone>
     */
    public function getPhones(): array
    {
        return $this->phones;
    }
}
