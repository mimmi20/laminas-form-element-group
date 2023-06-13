<?php
/**
 * This file is part of the mimmi20/laminas-form-element-group package.
 *
 * Copyright (c) 2021-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20Test\Form\Element\Group;

use ArrayAccess;
use ArrayObject;
use Laminas\Form\Element;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Mimmi20\Form\Element\Group\ElementGroup;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use stdClass;

use function assert;
use function property_exists;
use function sprintf;

final class ElementGroup4Test extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-3')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface3(): void
    {
        $count = 2;

        $form = new Form('test');
        $text = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => $count,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'target_element' => $text,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount($count, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount($count, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);

        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount($count, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount($count, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-4')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface4(): void
    {
        $form = new Form('test');
        $text = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'target_element' => $text,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(2, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(2, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);

        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(2, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(2, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-3')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface5(): void
    {
        $form = new Form('test');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 2,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(0, $result['text']);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(0, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-3')]
    #[Group('prepare-child-elements')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface6(): void
    {
        $form = new Form('test');
        $form->add(
            [
                'elements' => [
                    [
                        'spec' => [
                            'name' => 'text',
                            'type' => Element\Text::class,
                        ],
                    ],
                    [
                        'spec' => [
                            'name' => 'datesel',
                            'type' => Element\DateSelect::class,
                        ],
                    ],
                    [
                        'spec' => [
                            'name' => 'datetimesel',
                            'type' => Element\DateTimeSelect::class,
                        ],
                    ],
                    [
                        'spec' => [
                            'name' => 'num',
                            'type' => Element\Number::class,
                        ],
                    ],
                ],
                'name' => 'text',
                'type' => ElementGroup::class,
            ],
        );

        $form->prepare();

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(4, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(4, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-3')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface7(): void
    {
        $count = 2;

        $form = new Form('test');
        $text = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'target_element' => $text,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount($count, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount($count, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-4')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface8(): void
    {
        $form = new Form('test');
        $text = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'target_element' => $text,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(2, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(2, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-3')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface9(): void
    {
        $form = new Form('test');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(0, $result['text']);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(0, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-4')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface10(): void
    {
        $form = new Form('test');
        $text = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => false,
                    'target_element' => $text,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(2, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(2, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-4')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface11(): void
    {
        $form = new Form('test');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => true,
                    'target_element' => null,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(0, $result['text']);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(0, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(0, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-4')]
    #[Group('prepare-template-element')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface12(): void
    {
        $form = new Form('test');
        $text = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => 0,
                    'create_new_objects' => false,
                    'should_create_template' => false,
                    'target_element' => $text,
                    'template_placeholder' => 'template_counter',
                ],
                'type' => ElementGroup::class,
            ],
        );

        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(2, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(2, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);

        $form->prepare();
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount(2, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('2020-01-01', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('2021-01-01', $result['text'][1]);

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(2, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(2, $elements2);
        self::assertContainsOnlyInstancesOf(Element\DateSelect::class, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    #[Group('test-3')]
    #[Group('prepare-child-elements')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface13(): void
    {
        $form = new Form('test');

        $textField = $this->createMock(Element\Text::class);
        $textField->expects(self::once())
            ->method('getName')
            ->willReturn('text-field');

        $dateselectField = $this->createMock(Element\DateSelect::class);
        $dateselectField->expects(self::once())
            ->method('prepareElement')
            ->with($form);
        $dateselectField->expects(self::once())
            ->method('getName')
            ->willReturn('date-select-field');

        $datetimeselectField = $this->createMock(Element\DateTimeSelect::class);
        $datetimeselectField->expects(self::once())
            ->method('prepareElement')
            ->with($form);
        $datetimeselectField->expects(self::once())
            ->method('getName')
            ->willReturn('datetime-select-field');

        $numberField = $this->createMock(Element\Number::class);
        $numberField->expects(self::once())
            ->method('getName')
            ->willReturn('number-field');

        $elementGroup = new ElementGroup();
        $elementGroup->setName('text');
        $elementGroup->add($textField);
        $elementGroup->add($dateselectField);
        $elementGroup->add($datetimeselectField);
        $elementGroup->add($numberField);

        $form->add($elementGroup);
        $form->prepare();

        $elements = $form->getElements();
        self::assertIsArray($elements);
        self::assertCount(0, $elements);

        $fieldsets = $form->getFieldsets();
        self::assertIsArray($fieldsets);
        self::assertCount(1, $fieldsets);

        $fieldset = $form->get('text');
        self::assertInstanceOf(ElementGroup::class, $fieldset);
        self::assertCount(4, $fieldset);

        $elements2 = $fieldset->getElements();
        self::assertIsArray($elements2);
        self::assertCount(4, $elements2);

        $fieldsets2 = $fieldset->getFieldsets();
        self::assertIsArray($fieldsets2);
        self::assertCount(0, $fieldsets2);
    }

    /**
     * Unit test to ensure behavior of extract() method is unaffected by refactor
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    public function testCollectionShouldSilentlyIgnorePopulatingFieldsetWithDisallowedObject(): void
    {
        $mainFieldset = new Fieldset();
        $mainFieldset->add(new Element\Text('test'));
        $mainFieldset->setObject(new ArrayObject());
        $mainFieldset->setHydrator(new ObjectPropertyHydrator());

        $form = new Form();
        $form->setObject(new stdClass());
        $form->setHydrator(new ObjectPropertyHydrator());
        $form->add(
            [
                'name' => 'collection',
                'options' => [
                    'count' => 2,
                    'target_element' => $mainFieldset,
                ],
                'type' => ElementGroup::class,
            ],
        );

        $model             = new stdClass();
        $model->collection = [new ArrayObject(['test' => 'bar']), new stdClass()];

        $form->bind($model);
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf('stdClass', $result);
        self::assertTrue(property_exists($result, 'collection'));
        self::assertIsArray($result->collection);
        self::assertCount(1, $result->collection);
        self::assertInstanceOf('ArrayObject', $result->collection[0]);
        self::assertArrayHasKey('test', $result->collection[0]);
        self::assertSame('bar', $result->collection[0]['test']);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6298')]
    public function testCanHydrateObject(): void
    {
        $color  = '#ffffff';
        $form   = new FormCollection();
        $object = new ArrayObject();
        $form->bind($object);
        $data = [
            'colors' => [$color],
        ];
        $form->setData($data);
        self::assertTrue($form->isValid());
        self::assertIsArray($object['colors']);
        self::assertCount(1, $object['colors']);
        self::assertSame($color, $object['colors'][0]);
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testCanRemoveMultipleElements(): void
    {
        $form       = new FormCollection();
        $collection = $form->get('colors');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection::class,
            ),
        );

        $collection->setAllowRemove(true);
        $collection->setCount(0);

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';
        $data[] = 'red';

        $collection->populateValues($data);

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(2, $collLastIndex->getValue($collection));

        $collection->populateValues(['colors' => ['0' => 'blue']]);
        self::assertCount(1, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(2, $collLastIndex->getValue($collection));
    }
}
