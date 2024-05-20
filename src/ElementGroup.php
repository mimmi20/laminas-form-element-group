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

namespace Mimmi20\Form\Element\Group;

use Laminas\Form\Element\Collection;
use Laminas\Form\ElementInterface;
use Laminas\Form\ElementPrepareAwareInterface;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\FormInterface;
use Laminas\Stdlib\ArrayUtils;
use Traversable;

use function array_key_exists;
use function assert;
use function count;
use function is_array;
use function is_countable;
use function is_int;
use function is_iterable;
use function is_scalar;
use function sprintf;

final class ElementGroup extends Collection
{
    /**
     * Ensures state is ready for use.
     *
     * @param FormInterface<mixed> $form
     *
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function prepareElement(FormInterface $form): void
    {
        $fieldCount = 0;

        if ($this->targetElement !== null) {
            if ($this->shouldCreateChildrenOnPrepareElement && $this->count >= 1) {
                while ($this->count > $this->lastChildIndex + 1) {
                    $this->addNewTargetElementInstance((string) ++$this->lastChildIndex);
                }
            }

            $fieldCount = $this->count();

            // Create a template that will also be prepared
            if ($this->shouldCreateTemplate) {
                $templateElement = $this->getTemplateElement();

                if ($templateElement !== null) {
                    $this->add($templateElement);

                    assert($fieldCount + 1 === $this->count());
                }
            }
        }

        foreach ($this->iterator as $elementOrFieldset) {
            // Recursively prepare elements
            if (!$elementOrFieldset instanceof ElementPrepareAwareInterface) {
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

        assert($fieldCount === $this->count());
    }

    /**
     * Populate values
     *
     * @phpstan-param iterable<mixed> $data
     *
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function populateValues(iterable $data): void
    {
        if ($data instanceof Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }

        if (!$this->allowRemove && (is_countable($data) ? count($data) : 0) < $this->count) {
            throw new DomainException(sprintf(
                'There are fewer elements than specified in the collection (%s). Either set the allow_remove option '
                . 'to true, or re-submit the form.',
                self::class,
            ));
        }

        /**
         * Check to see if elements have been replaced or removed
         *
         * @var array<int, int|string> $toRemove
         */
        $toRemove = [];

        foreach ($this as $name => $elementOrFieldset) {
            if (array_key_exists($name, $data) || $elementOrFieldset instanceof self) {
                continue;
            }

            if (!$this->allowRemove) {
                throw new DomainException(sprintf(
                    'Elements have been removed from the collection (%s) but the allow_remove option is not true.',
                    self::class,
                ));
            }

            $toRemove[] = $name;
        }

        foreach ($toRemove as $name) {
            $this->remove((string) $name);
        }

        foreach ($data as $key => $value) {
            $elementOrFieldset = null;

            if ($this->has((string) $key)) {
                $elementOrFieldset = $this->get((string) $key);
            } elseif ($this->targetElement) {
                $elementOrFieldset = $this->addNewTargetElementInstance((string) $key);

                if (is_int($key) && $key > $this->lastChildIndex) {
                    $this->lastChildIndex = $key;
                }
            }

            if ($elementOrFieldset instanceof FieldsetInterface && is_iterable($value)) {
                $elementOrFieldset->populateValues($value);

                continue;
            }

            if ($elementOrFieldset !== null && (is_scalar($value) || $value === null)) {
                $elementOrFieldset->setAttribute('value', $value);
            }
        }

        if (!$this->createNewObjects()) {
            $this->replaceTemplateObjects();
        }
    }

    /**
     * @return array<mixed>
     *
     * @throws InvalidArgumentException
     */
    public function extract(): array
    {
        if (!is_array($this->object)) {
            return [];
        }

        $values = [];

        foreach ($this->object as $key => $value) {
            // If a hydrator is provided, our work here is done
            if ($this->hydrator) {
                $values[$key] = $this->hydrator->extract($value);

                continue;
            }

            // If the target element is a fieldset that can accept the provided value
            // we should clone it, inject the value and extract the data
            if ($this->targetElement instanceof FieldsetInterface) {
                if (!$this->targetElement->allowObjectBinding($value)) {
                    continue;
                }

                $targetElement = clone $this->targetElement;
                assert($targetElement instanceof Fieldset);
                $targetElement->setObject($value);
                $values[$key] = $targetElement->extract();

                if (!$this->createNewObjects() && $this->has((string) $key)) {
                    $fieldset = $this->get((string) $key);
                    assert($fieldset instanceof FieldsetInterface);
                    $fieldset->setObject($value);
                }

                continue;
            }

            // If the target element is a non-fieldset element, just use the value
            $values[$key] = $value;

            if (!$this->targetElement instanceof ElementInterface) {
                continue;
            }

            if (!$this->createNewObjects() && $this->has((string) $key)) {
                $this->get((string) $key)->setValue($value);
            }
        }

        return $values;
    }
}
