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
use Laminas\InputFilter\InputFilterProviderInterface;
use Override;

final class NestedFieldset extends Fieldset implements InputFilterProviderInterface
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('nested_fieldset');

        $field = new Element('anotherField', ['label' => 'Name']);
        $field->setAttribute('type', 'text');

        $this->add($field);
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
            'anotherField' => ['required' => true],
        ];
    }
}
