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

use Laminas\Form\Element;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;

final class AddressFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('address');

        $this
            ->setHydrator(new ClassMethodsHydrator(false))
            ->setObject(new Entity\Address());

        $street = new Element('street', ['label' => 'Street']);
        $street->setAttribute('type', 'text');

        $city = new CityFieldset();
        $city->setLabel('City');

        $this->add($street);
        $this->add($city);

        $phones = new Element\Collection('phones');
        $phones->setLabel('Phone numbers')
            ->setOptions(
                [
                    'allow_add' => true,
                    'allow_remove' => true,
                    'count' => 2,
                    'target_element' => new PhoneFieldset(),
                ],
            );
        $this->add($phones);
    }

    /**
     * Should return an array specification compatible with
     * {@link \Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array<string, array<string, bool>>
     *
     * @throws void
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'street' => ['required' => true],
        ];
    }
}
