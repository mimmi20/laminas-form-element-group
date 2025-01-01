<?php

/**
 * This file is part of the mimmi20/laminas-form-element-group package.
 *
 * Copyright (c) 2021-2025, Thomas Mueller <mimmi20@live.de>
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
use Override;

final class CityFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('city');

        $this
            ->setHydrator(new ClassMethodsHydrator(false))
            ->setObject(new Entity\City());

        $name = new Element('name', ['label' => 'Name of the city']);
        $name->setAttribute('type', 'text');

        $zipCode = new Element('zipCode', ['label' => 'ZipCode of the city']);
        $zipCode->setAttribute('type', 'text');

        $country = new CountryFieldset();
        $country->setLabel('Country');

        $this->add($name);
        $this->add($zipCode);
        $this->add($country);
    }

    /**
     * Should return an array specification compatible with
     * {@link \Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array<string, array<string, bool>>
     *
     * @throws void
     */
    #[Override]
    public function getInputFilterSpecification(): array
    {
        return [
            'name' => ['required' => true],
            'zipCode' => ['required' => true],
        ];
    }
}
