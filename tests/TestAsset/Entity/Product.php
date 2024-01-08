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

use function assert;
use function get_object_vars;
use function is_array;

final class Product
{
    private string $name = '';
    private int $price   = 0;

    /** @var array<int, Category> */
    private array $categories             = [];
    private Country | null $madeInCountry = null;

    /**
     * @param array<int, Category> $categories
     *
     * @throws void
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return array<int, Category>
     *
     * @throws void
     */
    public function getCategories(): array
    {
        return $this->categories;
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
    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /** @throws void */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * Return category from index
     *
     * @throws void
     */
    public function getCategory(int $i): Category
    {
        return $this->categories[$i];
    }

    /**
     * Required when binding to a form
     *
     * @phpstan-return array{name: string, price: int, categories: array<int, Category>, madeInCountry: Country|null}
     *
     * @throws void
     */
    public function getArrayCopy(): array
    {
        /** @phpstan-var array{name: string, price: int, categories: array<int, Category>, madeInCountry: Country|null} $vars */
        $vars = get_object_vars($this);

        assert(is_array($vars));

        return $vars;
    }

    /** @throws void */
    public function getMadeInCountry(): Country | null
    {
        return $this->madeInCountry;
    }

    /** @throws void */
    public function setMadeInCountry(Country $country): void
    {
        $this->madeInCountry = $country;
    }
}
