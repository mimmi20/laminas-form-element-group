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

use function get_object_vars;

final class Product
{
    private string $name = '';

    private int $price = 0;

    /** @var array<int, Category> */
    private array $categories = [];

    private ?Country $madeInCountry = null;

    /**
     * @param array<int, Category> $categories
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return array<int, Category>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * Return category from index
     */
    public function getCategory(int $i): Category
    {
        return $this->categories[$i];
    }

    /**
     * Required when binding to a form
     *
     * @return array<string, array<int, Category>|Country|int|string|null>
     */
    public function getArrayCopy(): array
    {
        return get_object_vars($this);
    }

    public function getMadeInCountry(): ?Country
    {
        return $this->madeInCountry;
    }

    public function setMadeInCountry(Country $country): void
    {
        $this->madeInCountry = $country;
    }
}
