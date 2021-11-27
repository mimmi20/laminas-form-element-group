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

use Laminas\Form\Fieldset;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;
use Mimmi20\Form\Element\Group\ElementGroup;

final class ProductFieldset extends Fieldset implements InputFilterProviderInterface
{
    private const NAME     = 'name';
    private const OPTIONS  = 'options';
    private const LABEL    = 'label';
    private const REQUIRED = 'required';
    private const TYPE     = 'type';

    public function __construct()
    {
        parent::__construct('product');
        $this
            ->setHydrator(new ClassMethodsHydrator())
            ->setObject(new Entity\Product());

        $this->add([
            self::NAME => self::NAME,
            self::OPTIONS => [self::LABEL => 'Name of the product'],
            'attributes' => [self::REQUIRED => self::REQUIRED],
        ]);

        $this->add([
            self::NAME => 'price',
            self::OPTIONS => [self::LABEL => 'Price of the product'],
            'attributes' => [self::REQUIRED => self::REQUIRED],
        ]);

        $this->add([
            self::TYPE => ElementGroup::class,
            self::NAME => 'categories',
            self::OPTIONS => [
                self::LABEL => 'Please choose categories for this product',
                'count' => 2,
                'target_element' => [
                    self::TYPE => CategoryFieldset::class,
                ],
            ],
        ]);

        $this->add([
            self::TYPE => CountryFieldset::class,
            self::NAME => 'made_in_country',
            'object' => Entity\Country::class,
            'hydrator' => ClassMethodsHydrator::class,
            self::OPTIONS => [self::LABEL => 'Please choose the country'],
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
            self::NAME => [self::REQUIRED => true],
            'price' => [
                self::REQUIRED => true,
                'validators' => [
                    [self::NAME => 'IsFloat'],
                ],
            ],
            'made_in_country' => [self::REQUIRED => false],
        ];
    }
}
