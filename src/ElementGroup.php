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

use Laminas\Form\Element\Collection;
use Laminas\Form\ElementPrepareAwareInterface;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\FormInterface;

final class ElementGroup extends Collection
{
    /**
     * Ensures state is ready for use.
     *
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function prepareElement(FormInterface $form): void
    {
        if (true === $this->shouldCreateChildrenOnPrepareElement) {
            if (null !== $this->targetElement && 0 < $this->count) {
                while ($this->count > $this->lastChildIndex + 1) {
                    $this->addNewTargetElementInstance((string) ++$this->lastChildIndex);
                }
            }
        }

        // Create a template that will also be prepared
        if ($this->shouldCreateTemplate) {
            $templateElement = $this->getTemplateElement();

            if (null !== $templateElement) {
                $this->add($templateElement);
            }
        }

        foreach ($this->iterator as $elementOrFieldset) {
            // Recursively prepare elements
            if (!($elementOrFieldset instanceof ElementPrepareAwareInterface)) {
                continue;
            }

            $elementOrFieldset->prepareElement($form);
        }

        // The template element has been prepared, but we don't want it to be
        // rendered nor validated, so remove it from the list.
        if (!$this->shouldCreateTemplate) {
            return;
        }

        $this->remove($this->templatePlaceholder);
    }
}
