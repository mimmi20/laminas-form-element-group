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

namespace Mimmi20\Form\Element\Group;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

final class Module implements ConfigProviderInterface, DependencyIndicatorInterface
{
    /**
     * Return default configuration for laminas-mvc applications.
     *
     * @return array<string, array<string, array<int|string, string>>>
     * @phpstan-return array{form_elements: array{aliases: array<string, class-string>, factories: array<class-string, class-string>}}
     */
    public function getConfig(): array
    {
        $provider = new ConfigProvider();

        return [
            'form_elements' => $provider->getFormElementConfig(),
        ];
    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array<int, string>
     */
    public function getModuleDependencies(): array
    {
        return [
            'Laminas\I18n',
            'Laminas\Hydrator',
            'Laminas\Validator',
            'Laminas\InputFilter',
            'Laminas\Form',
        ];
    }
}
