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

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

final class BasicFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('basic_fieldset');

        $field = new Element('field', ['label' => 'Name']);
        $field->setAttribute('type', 'text');
        $this->add($field);

        $nestedFieldset = new NestedFieldset();
        $this->add($nestedFieldset);
    }

    /**
     * Should return an array specification compatible with
     * {@link \Laminas\InputFilter\Factory::createInputFilter()}.
     *
     * @return array<string, array<string, bool>>
     */
    public function getInputFilterSpecification(): array
    {
        return [
            'field' => ['required' => true],
        ];
    }
}
