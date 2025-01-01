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

use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\InputFilter\InputFilterProviderInterface;
use Override;

final class CategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('category');

        $this
            ->setHydrator(new ClassMethodsHydrator())
            ->setObject(new Entity\Category());

        $this->add([
            'attributes' => ['required' => 'required'],
            'name' => 'name',
            'options' => ['label' => 'Name of the category'],
        ]);
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
        ];
    }
}
