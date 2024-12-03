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

use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;
use Mimmi20\Form\Element\Group\ElementGroup;

final class ProductFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('product');

        $this
            ->setHydrator(new ClassMethodsHydrator())
            ->setObject(new Entity\Product());

        $this->add([
            'attributes' => ['required' => 'required'],
            'name' => 'name',
            'options' => ['label' => 'Name of the product'],
        ]);

        $this->add([
            'attributes' => ['required' => 'required'],
            'name' => 'price',
            'options' => ['label' => 'Price of the product'],
        ]);

        $this->add([
            'name' => 'categories',
            'options' => [
                'count' => 2,
                'label' => 'Please choose categories for this product',
                'target_element' => [
                    'type' => CategoryFieldset::class,
                ],
            ],
            'type' => ElementGroup::class,
        ]);

        $this->add([
            'hydrator' => ClassMethodsHydrator::class,
            'name' => 'made_in_country',
            'object' => Entity\Country::class,
            'options' => ['label' => 'Please choose the country'],
            'type' => CountryFieldset::class,
        ]);
    }

    /**
     * Should return an array specification compatible with
     * {@link \Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array<string, array<string, array<int, array<string, string>>|bool>>
     *
     * @throws void
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'made_in_country' => ['required' => false],
            'name' => ['required' => true],
            'price' => [
                'required' => true,
                'validators' => [
                    ['name' => 'IsFloat'],
                ],
            ],
        ];
    }
}
