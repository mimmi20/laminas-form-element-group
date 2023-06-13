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

use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\InputFilter\ArrayInput;
use Mimmi20\Form\Element\Group\ElementGroup;
use Mimmi20Test\Form\Element\Group\TestAsset\ArrayModel;
use Mimmi20Test\Form\Element\Group\TestAsset\CustomTraversable;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;

use function assert;
use function get_debug_type;
use function sprintf;

final class ElementGroup5Test extends TestCase
{
    /**
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('removal-not-allowed')]
    #[Group('test-populate-values')]
    public function testCanNotRemoveMultipleElements(): void
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

        $collection->setAllowRemove(false);
        $collection->setCount(0);

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';
        $data[] = 'red';

        $collection->populateValues($data);

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(2, $collLastIndex->getValue($collection));

        $this->expectException(DomainException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Elements have been removed from the collection (Mimmi20\Form\Element\Group\ElementGroup) but the allow_remove option is not true.',
        );

        $collection->populateValues(['colors' => ['0' => 'blue']]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\InputFilter\Exception\InvalidArgumentException
     */
    public function testGetErrorMessagesForInvalidCollectionElements(): void
    {
        $form = new FormCollection();

        // Configure InputFilter
        $inputFilter = $form->getInputFilter();
        $inputFilter->add(
            [
                'name' => 'colors',
                'required' => true,
                'type' => ArrayInput::class,
            ],
        );
        $inputFilter->add(
            [
                'name' => 'fieldsets',
                'required' => true,
                'type' => ArrayInput::class,
            ],
        );

        $form->setData([]);
        $form->isValid();

        self::assertSame(
            [
                'colors' => ['isEmpty' => 'Value is required and can\'t be empty'],
                'fieldsets' => ['isEmpty' => 'Value is required and can\'t be empty'],
            ],
            $form->getMessages(),
        );
    }

    /**
     * @see https://github.com/zendframework/zend-form/pull/230
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testNullTargetElementShouldResultInEmptyData(): void
    {
        $form = new Form();

        $form->add(
            [
                'name' => 'fieldsets',
                'options' => ['count' => 2],
                'type' => ElementGroup::class,
            ],
        );

        $data = [
            'fieldsets' => [
                'red',
                'green',
                'blue',
            ],
        ];

        $form->setData($data);
        $form->isValid();

        // expect the fieldsets key to be an empty array since there's no valid targetElement
        self::assertSame(
            [
                'fieldsets' => [],
            ],
            $form->getData(),
        );
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testPopulateValuesTraversable(): void
    {
        $data = new CustomTraversable(['blue', 'green']);

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

        $collection->setAllowRemove(false);
        $collection->populateValues($data);

        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \DomainException
     */
    public function testSetObjectTraversable(): void
    {
        $form       = new FormCollection();
        $collection = $form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection::class,
            ),
        );

        // this test is using a hydrator set on the target element of the collection
        $targetElement = $collection->getTargetElement();
        assert(
            $targetElement instanceof FieldsetInterface,
            sprintf(
                '$targetElement should be an Instance of %s, but was %s',
                FieldsetInterface::class,
                get_debug_type($targetElement),
            ),
        );

        $targetElement->setHydrator(new ArraySerializableHydrator());
        $obj1 = new ArrayModel();
        $targetElement->setObject($obj1);

        $obj2 = new ArrayModel();
        $obj2->exchangeArray(
            ['bar' => 'bar_value_1', 'foo' => 'foo_value_1', 'foobar' => 'foobar_value_1'],
        );
        $obj3 = new ArrayModel();
        $obj3->exchangeArray(
            ['bar' => 'bar_value_2', 'foo' => 'foo_value_2', 'foobar' => 'foobar_value_2'],
        );

        $collection->setObject(new CustomTraversable([$obj2, $obj3]));

        $expected = [
            ['bar' => 'bar_value_1', 'foo' => 'foo_value_1', 'foobar' => 'foobar_value_1'],
            ['bar' => 'bar_value_2', 'foo' => 'foo_value_2', 'foobar' => 'foobar_value_2'],
        ];

        self::assertSame($expected, $collection->extract());
        self::assertSame([$obj2, $obj3], $collection->getObject());
    }
}
