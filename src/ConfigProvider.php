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

namespace Mimmi20\Form\Element;

use Laminas\Form\ElementFactory;

final class ConfigProvider
{
    /**
     * Return general-purpose laminas-navigation configuration.
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public function __invoke(): array
    {
        return [
            'form_elements' => $this->getFormElementConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, array<string, string>>
     */
    public function getFormElementConfig(): array
    {
        return [
            'aliases' => [
                'elementGroup' => ElementGroup::class,
                'element_group' => ElementGroup::class,
            ],
            'factories' => [
                ElementGroup::class => ElementFactory::class,
            ],
        ];
    }
}
