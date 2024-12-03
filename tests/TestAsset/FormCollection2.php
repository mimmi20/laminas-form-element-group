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
use Laminas\Form\Form;
use Mimmi20\Form\Element\Group\ElementGroup;

/** @extends Form<mixed> */
final class FormCollection2 extends Form
{
    /** @throws InvalidArgumentException */
    public function __construct()
    {
        parent::__construct('collection');

        $this->setInputFilter(new InputFilter());

        $this->add(
            [
                'name' => 'colors',
                'options' => ['count' => 2],
                'type' => ElementGroup::class,
            ],
        );

        $fieldset = new BasicFieldset();
        $this->add(
            [
                'name' => 'fieldsets',
                'options' => [
                    'count' => 2,
                    'target_element' => $fieldset,
                ],
                'type' => ElementGroup::class,
            ],
        );
    }
}
