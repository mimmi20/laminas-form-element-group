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

namespace Mimmi20Test\Form\Element\TestAsset;

use Laminas\Form\Fieldset;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;
use Mimmi20\Form\Element\ElementGroup;

final class ProductFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('product');
        $this
            ->setHydrator(new ClassMethodsHydrator())
            ->setObject(new Entity\Product());

        $this->add([
            'name' => 'name',
            'options' => ['label' => 'Name of the product'],
            'attributes' => ['required' => 'required'],
        ]);

        $this->add([
            'name' => 'price',
            'options' => ['label' => 'Price of the product'],
            'attributes' => ['required' => 'required'],
        ]);

        $this->add([
            'type' => ElementGroup::class,
            'name' => 'categories',
            'options' => [
                'label' => 'Please choose categories for this product',
                'count' => 2,
                'target_element' => [
                    'type' => CategoryFieldset::class,
                ],
            ],
        ]);

        $this->add([
            'type' => CountryFieldset::class,
            'name' => 'made_in_country',
            'object' => Entity\Country::class,
            'hydrator' => ClassMethodsHydrator::class,
            'options' => ['label' => 'Please choose the country'],
        ]);
    }

    /**
     * Should return an array specification compatible with
     * {@link \Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array<string, array<string, array<int, array<string, string>>|bool>>
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'name' => ['required' => true],
            'price' => [
                'required' => true,
                'validators' => [
                    ['name' => 'IsFloat'],
                ],
            ],
            'made_in_country' => ['required' => false],
        ];
    }
}
