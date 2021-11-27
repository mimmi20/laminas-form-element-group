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

final class CategoryFieldset extends Fieldset implements InputFilterProviderInterface
{
    private const NAME     = 'name';
    private const REQUIRED = 'required';

    public function __construct()
    {
        parent::__construct('category');
        $this
            ->setHydrator(new ClassMethodsHydrator())
            ->setObject(new Entity\Category());

        $this->add([
            self::NAME => self::NAME,
            'options' => ['label' => 'Name of the category'],
            'attributes' => [self::REQUIRED => self::REQUIRED],
        ]);
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
            self::NAME => [self::REQUIRED => true],
        ];
    }
}
