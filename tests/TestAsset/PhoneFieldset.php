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

final class PhoneFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('phones');

        $this
            ->setHydrator(new ClassMethodsHydrator())
            ->setObject(new Entity\Phone());

        $id = new Element\Hidden('id');
        $this->add($id);

        $number = new Element\Text('number');
        $number->setLabel('Number')
            ->setAttribute('class', 'form-control');
        $this->add($number);
    }

    /**
     * @return array<string, array<string, bool>>
     *
     * @throws void
     */
    #[Override]
    public function getInputFilterSpecification(): array
    {
        return [
            'number' => ['required' => true],
        ];
    }
}
