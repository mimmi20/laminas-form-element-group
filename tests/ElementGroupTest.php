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

namespace Mimmi20Test\Form\Element\Group;

use ArrayAccess;
use ArrayObject;
use Laminas\Form\Element;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Laminas\InputFilter\ArrayInput;
use Mimmi20\Form\Element\Group\ElementGroup;
use Mimmi20Test\Form\Element\Group\TestAsset\AddressFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\ArrayModel;
use Mimmi20Test\Form\Element\Group\TestAsset\CategoryFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\CountryFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\CustomCollection;
use Mimmi20Test\Form\Element\Group\TestAsset\CustomTraversable;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Address;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Category;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\City;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Country;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Phone;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Product;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection2;
use Mimmi20Test\Form\Element\Group\TestAsset\PhoneFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\ProductFieldset;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use stdClass;

use function assert;
use function count;
use function extension_loaded;
use function get_debug_type;
use function is_array;
use function iterator_count;
use function property_exists;
use function spl_object_hash;
use function sprintf;

final class ElementGroupTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanRetrieveDefaultPlaceholder(): void
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

        $placeholder = $collection->getTemplatePlaceholder();
        self::assertSame('__index__', $placeholder);
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testCannotAllowNewElementsIfAllowAddIsFalse(): void
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

        self::assertTrue($collection->allowAdd());
        $collection->setAllowAdd(false);
        self::assertFalse($collection->allowAdd());

        // By default, $collection contains 2 elements
        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));

        $this->expectException(DomainException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'There are more elements than specified in the collection (Mimmi20\Form\Element\Group\ElementGroup). Either set the allow_add option to true, or re-submit the form.',
        );

        $data[] = 'orange';
        $collection->populateValues($data);
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testCanAddNewElementsIfAllowAddIsTrue(): void
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

        $collection->setAllowAdd(true);
        self::assertTrue($collection->allowAdd());

        // By default, $collection contains 2 elements
        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));

        $data[] = 'orange';
        $collection->populateValues($data);
        self::assertCount(3, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(2, $collLastIndex->getValue($collection));
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testCanRemoveElementsIfAllowRemoveIsTrue(): void
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
        self::assertTrue($collection->allowRemove());

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));

        unset($data[0]);

        $collection->populateValues($data);
        self::assertCount(1, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testCanReplaceElementsIfAllowAddAndAllowRemoveIsTrue(): void
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

        $collection->setAllowAdd(true);
        $collection->setAllowRemove(true);

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));

        unset($data[0]);
        $data[] = 'orange';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(2, $collLastIndex->getValue($collection));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanValidateFormWithCollectionWithoutTemplate(): void
    {
        $form = new FormCollection();
        $form->setData(
            [
                'colors' => [
                    '#ffffff',
                    '#ffffff',
                ],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                    [
                        'field' => 'twoValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                ],
            ],
        );

        self::assertTrue($form->isValid());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCannotValidateFormWithCollectionWithBadColor(): void
    {
        $form = new FormCollection();
        $form->setData(
            [
                'colors' => [
                    '#ffffff',
                    '123465',
                ],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                    [
                        'field' => 'twoValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                ],
            ],
        );

        self::assertFalse($form->isValid());
        $messages = $form->getMessages();

        self::assertArrayHasKey('colors', $messages);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCannotValidateFormWithCollectionWithBadFieldsetField(): void
    {
        $form = new FormCollection();
        $form->setData(
            [
                'colors' => [
                    '#ffffff',
                    '#ffffff',
                ],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                    [
                        'field' => 'twoValue',
                        'nested_fieldset' => ['anotherField' => null],
                    ],
                ],
            ],
        );

        self::assertFalse($form->isValid());
        $messages = $form->getMessages();

        self::assertCount(1, $messages);
        self::assertArrayHasKey('fieldsets', $messages);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanValidateFormWithCollectionWithTemplate(): void
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

        self::assertFalse($collection->shouldCreateTemplate());
        $collection->setShouldCreateTemplate(true);
        self::assertTrue($collection->shouldCreateTemplate());

        $collection->setTemplatePlaceholder('__template__');

        $form->setData(
            [
                'colors' => [
                    '#ffffff',
                    '#ffffff',
                ],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                    [
                        'field' => 'twoValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                ],
            ],
        );

        self::assertTrue($form->isValid());
    }

    /** @throws InvalidArgumentException */
    #[Group('removal-not-allowed')]
    public function testThrowExceptionIfThereAreLessElementsAndAllowRemoveNotAllowed(): void
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

        $this->expectException(DomainException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'There are fewer elements than specified in the collection (Mimmi20\Form\Element\Group\ElementGroup). Either set the allow_remove option to true, or re-submit the form.',
        );

        $form->setData(
            [
                'colors' => ['#ffffff'],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                    [
                        'field' => 'twoValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                ],
            ],
        );
    }

    /** @throws InvalidArgumentException */
    #[Group('removal-not-allowed')]
    public function testThrowExceptionIfThereAreLessElementsAndAllowRemoveNotAllowed2(): void
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

        $collection->setAllowRemove(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'There are fewer elements than specified in the collection (Mimmi20\Form\Element\Group\ElementGroup). Either set the allow_remove option to true, or re-submit the form.',
        );

        $form->setData(
            [
                'colors' => ['#ffffff'],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                ],
            ],
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanValidateLessThanSpecifiedCount(): void
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

        $form->setData(
            [
                'colors' => ['#ffffff'],
                'fieldsets' => [
                    [
                        'field' => 'oneValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                    [
                        'field' => 'twoValue',
                        'nested_fieldset' => ['anotherField' => 'anotherValue'],
                    ],
                ],
            ],
        );

        self::assertTrue($form->isValid());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetOptions(): void
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

        $element = new Element('foo');
        $collection->setOptions(
            [
                'allow_add' => true,
                'allow_remove' => false,
                'count' => 2,
                'should_create_template' => true,
                'target_element' => $element,
                'template_placeholder' => 'foo',
            ],
        );

        self::assertInstanceOf(Element::class, $collection->getOption('target_element'));
        self::assertSame(2, $collection->getOption('count'));
        self::assertTrue($collection->getOption('allow_add'));
        self::assertFalse($collection->getOption('allow_remove'));
        self::assertTrue($collection->getOption('should_create_template'));
        self::assertSame('foo', $collection->getOption('template_placeholder'));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetOptionsTraversable(): void
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

        $element = new Element('foo');
        $collection->setOptions(
            new CustomTraversable(
                [
                    'allow_add' => true,
                    'allow_remove' => false,
                    'count' => 2,
                    'should_create_template' => true,
                    'target_element' => $element,
                    'template_placeholder' => 'foo',
                ],
            ),
        );

        self::assertInstanceOf(Element::class, $collection->getOption('target_element'));
        self::assertSame(2, $collection->getOption('count'));
        self::assertTrue($collection->getOption('allow_add'));
        self::assertFalse($collection->getOption('allow_remove'));
        self::assertTrue($collection->getOption('should_create_template'));
        self::assertSame('foo', $collection->getOption('template_placeholder'));
    }

    /** @throws InvalidArgumentException */
    public function testSetObjectNullRaisesException(): void
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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Laminas\Form\Element\Collection::setObject expects an array or Traversable object argument; received "null"',
        );

        $collection->setObject(null);
    }

    /** @throws InvalidArgumentException */
    public function testSetTargetElementNullRaisesException(): void
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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage(
            'Laminas\Form\Element\Collection::setTargetElement requires that $elementOrFieldset be an object implementing Laminas\Form\Element\ElementInterface; received "null"',
        );

        $collection->setTargetElement(null);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetTargetElement(): void
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

        $element = new Element('foo');
        $collection->setTargetElement($element);

        self::assertInstanceOf(Element::class, $collection->getTargetElement());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testExtractFromObjectDoesntTouchOriginalObject(): void
    {
        $form = new Form();
        $form->setHydrator(new ClassMethodsHydrator());

        $productFieldset = new ProductFieldset();
        $productFieldset->setUseAsBaseFieldset(true);

        $form->add($productFieldset);

        $collection = $productFieldset->get('categories');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection::class,
            ),
        );

        $originalObjectHash = spl_object_hash(
            $collection->getTargetElement()->getObject(),
        );

        $product = new Product();
        $product->setName('foo');
        $product->setPrice(42);
        $cat1 = new Category();
        $cat1->setName('bar');
        $cat2 = new Category();
        $cat2->setName('bar2');

        $product->setCategories([$cat1, $cat2]);

        $form->bind($product);

        $form->setData(
            [
                'product' => [
                    'categories' => [
                        ['name' => 'sepp'],
                        ['name' => 'herbert'],
                    ],
                    'name' => 'franz',
                    'price' => 13,
                ],
            ],
        );

        $objectAfterExtractHash = spl_object_hash(
            $collection->getTargetElement()->getObject(),
        );

        self::assertSame($originalObjectHash, $objectAfterExtractHash);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testDoesNotCreateNewObjects(): void
    {
        if (!extension_loaded('intl')) {
            // Required by \Laminas\I18n\Validator\IsFloat
            self::markTestSkipped('ext/intl not enabled');
        }

        $form = new Form();
        $form->setHydrator(new ClassMethodsHydrator());

        $productFieldset = new ProductFieldset();
        $productFieldset->setUseAsBaseFieldset(true);

        $form->add($productFieldset);

        $product = new Product();
        $product->setName('foo');
        $product->setPrice(42);
        $cat1 = new Category();
        $cat1->setName('bar');
        $cat2 = new Category();
        $cat2->setName('bar2');

        $product->setCategories([$cat1, $cat2]);

        $form->bind($product);

        $form->setData(
            [
                'product' => [
                    'categories' => [
                        ['name' => 'sepp'],
                        ['name' => 'herbert'],
                    ],
                    'name' => 'franz',
                    'price' => 13,
                ],
            ],
        );
        $form->isValid();

        $categories = $product->getCategories();
        self::assertSame($categories[0], $cat1);
        self::assertSame($categories[1], $cat2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCreatesNewObjectsIfSpecified(): void
    {
        if (!extension_loaded('intl')) {
            // Required by \Laminas\I18n\Validator\IsFloat
            self::markTestSkipped('ext/intl not enabled');
        }

        $productFieldset = new ProductFieldset();
        $productFieldset->setUseAsBaseFieldset(true);

        $categories = $productFieldset->get('categories');
        $categories->setOptions(
            ['create_new_objects' => true],
        );

        $form = new Form();
        $form->setHydrator(new ClassMethodsHydrator());
        $form->add($productFieldset);

        $product = new Product();
        $product->setName('foo');
        $product->setPrice(42);
        $cat1 = new Category();
        $cat1->setName('bar');
        $cat2 = new Category();
        $cat2->setName('bar2');

        $product->setCategories([$cat1, $cat2]);

        $form->bind($product);

        $form->setData(
            [
                'product' => [
                    'categories' => [
                        ['name' => 'sepp'],
                        ['name' => 'herbert'],
                    ],
                    'name' => 'franz',
                    'price' => 13,
                ],
            ],
        );
        $form->isValid();

        $categories = $product->getCategories();
        self::assertNotSame($categories[0], $cat1);
        self::assertNotSame($categories[1], $cat2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6585')]
    #[Group('issue-6614')]
    public function testAddingCollectionElementAfterBind(): void
    {
        $form = new Form();
        $form->setHydrator(new ObjectPropertyHydrator());

        $phone = new PhoneFieldset();

        $form->add(
            [
                'name' => 'phones',
                'options' => [
                    'allow_add' => true,
                    'count' => 1,
                    'target_element' => $phone,
                ],
                'type' => ElementGroup::class,
            ],
        );

        $data = [
            'phones' => [
                ['number' => '1234567'],
                ['number' => '1234568'],
            ],
        ];

        $phone = new Phone();
        $phone->setNumber($data['phones'][0]['number']);

        $customer         = new stdClass();
        $customer->phones = [$phone];

        $form->bind($customer);
        $form->setData($data);
        self::assertTrue($form->isValid());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6585')]
    #[Group('issue-6614')]
    public function testDoesNotCreateNewObjectsWhenUsingNestedCollections(): void
    {
        $addressesFieldset = new AddressFieldset();
        $addressesFieldset->setHydrator(new ClassMethodsHydrator());
        $addressesFieldset->remove('city');

        $form = new Form();
        $form->setHydrator(new ObjectPropertyHydrator());
        $form->add(
            [
                'name' => 'addresses',
                'options' => [
                    'count' => 1,
                    'target_element' => $addressesFieldset,
                ],
                'type' => ElementGroup::class,
            ],
        );

        $data = [
            'addresses' => [
                [
                    'phones' => [
                        ['number' => '1234567'],
                    ],
                    'street' => 'street1',
                ],
            ],
        ];

        $phone = new Phone();
        $phone->setNumber($data['addresses'][0]['phones'][0]['number']);

        $address = new Address();
        $address->setStreet($data['addresses'][0]['street']);
        $address->setPhones([$phone]);

        $customer            = new stdClass();
        $customer->addresses = [$address];

        $form->bind($customer);
        $form->setData($data);

        self::assertTrue($form->isValid());
        $phones = $customer->addresses[0]->getPhones();
        self::assertSame($phone, $phones[0]);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testDoNotCreateExtraFieldsetOnMultipleBind(): void
    {
        $form = new Form();

        $productFieldset = new ProductFieldset();
        $productFieldset->setHydrator(new ClassMethodsHydrator());

        $form->add($productFieldset);
        $form->setHydrator(new ObjectPropertyHydrator());

        $product    = new Product();
        $categories = [
            new Category(),
            new Category(),
        ];
        $product->setCategories($categories);

        $market          = new stdClass();
        $market->product = $product;

        // this will pass the test
        $form->bind($market);
        self::assertSame(
            count($categories),
            iterator_count($form->get('product')->get('categories')->getIterator()),
        );

        // this won't pass, but must
        $form->bind($market);
        self::assertSame(
            count($categories),
            iterator_count($form->get('product')->get('categories')->getIterator()),
        );
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractDefaultIsEmptyArray(): void
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

        self::assertSame([], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractThroughTargetElementHydrator(): void
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

        $this->prepareForExtract($collection);

        $expected = [
            'obj2' => ['field' => 'fieldOne'],
            'obj3' => ['field' => 'fieldTwo'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractMaintainsTargetElementObject(): void
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

        $this->prepareForExtract($collection);

        $expected = $collection->getTargetElement()->getObject();

        $collection->extract();

        $test = $collection->getTargetElement()->getObject();

        self::assertSame($expected, $test);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractThroughCustomHydrator(): void
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

        $this->prepareForExtract($collection);

        $mockHydrator = $this->createMock(HydratorInterface::class);
        $mockHydrator->expects(self::exactly(2))
            ->method('extract')
            ->willReturnCallback(
                static fn (stdClass $object) => ['value' => $object->field . '_foo'],
            );

        $collection->setHydrator($mockHydrator);

        $expected = [
            'obj2' => ['value' => 'fieldOne_foo'],
            'obj3' => ['value' => 'fieldTwo_foo'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractFromTraversable(): void
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

        $this->prepareForExtract($collection);

        $arrayData = $collection->getObject();

        assert(is_array($arrayData));

        $traversable = new ArrayObject($arrayData);
        $collection->setObject($traversable);

        $expected = [
            'obj2' => ['field' => 'fieldOne'],
            'obj3' => ['field' => 'fieldTwo'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testValidateData(): void
    {
        $myFieldset = new Fieldset();
        $myFieldset->add(
            [
                'name' => 'email',
                'type' => 'Email',
            ],
        );

        $myForm = new Form();
        $myForm->add(
            [
                'name' => 'collection',
                'options' => ['target_element' => $myFieldset],
                'type' => ElementGroup::class,
            ],
        );

        $data = [
            'collection' => [
                ['email' => 'test1@test1.com'],
                ['email' => 'test2@test2.com'],
                ['email' => 'test3@test3.com'],
            ],
        ];

        $myForm->setData($data);

        self::assertTrue($myForm->isValid());
        self::assertEmpty($myForm->getMessages());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets(): void
    {
        $productFieldset = new ProductFieldset();
        $productFieldset->setHydrator(new ClassMethodsHydrator());

        $mainFieldset = new Fieldset();
        $mainFieldset->setObject(new stdClass());
        $mainFieldset->setHydrator(new ObjectPropertyHydrator());
        $mainFieldset->add($productFieldset);

        $form = new Form();
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

        $market = new stdClass();

        $prices           = [100, 200];
        $categoryNames    = ['electronics', 'furniture'];
        $productCountries = ['Russia', 'Jamaica'];

        $shop1          = new stdClass();
        $shop1->product = new Product();
        $shop1->product->setPrice($prices[0]);

        $category = new Category();
        $category->setName($categoryNames[0]);
        $shop1->product->setCategories([$category]);

        $country = new Country();
        $country->setName($productCountries[0]);
        $shop1->product->setMadeInCountry($country);

        $shop2          = new stdClass();
        $shop2->product = new Product();
        $shop2->product->setPrice($prices[1]);

        $category = new Category();
        $category->setName($categoryNames[1]);
        $shop2->product->setCategories([$category]);

        $country = new Country();
        $country->setName($productCountries[1]);
        $shop2->product->setMadeInCountry($country);

        $market->collection = [$shop1, $shop2];
        $form->bind($market);

        // test for object binding
        $_marketCollection = $form->get('collection');
        self::assertInstanceOf(ElementGroup::class, $_marketCollection);

        foreach ($_marketCollection as $_shopFieldset) {
            self::assertInstanceOf(Fieldset::class, $_shopFieldset);
            self::assertInstanceOf(stdClass::class, $_shopFieldset->getObject());

            // test for collection -> fieldset
            $_productFieldset = $_shopFieldset->get('product');
            self::assertInstanceOf(ProductFieldset::class, $_productFieldset);
            self::assertInstanceOf(Product::class, $_productFieldset->getObject());

            // test for collection -> fieldset -> fieldset
            self::assertInstanceOf(
                CountryFieldset::class,
                $_productFieldset->get('made_in_country'),
            );
            self::assertInstanceOf(
                Country::class,
                $_productFieldset->get('made_in_country')->getObject(),
            );

            // test for collection -> fieldset -> collection
            $_productCategories = $_productFieldset->get('categories');
            self::assertInstanceOf(ElementGroup::class, $_productCategories);

            // test for collection -> fieldset -> collection -> fieldset
            foreach ($_productCategories as $_category) {
                self::assertInstanceOf(CategoryFieldset::class, $_category);
                self::assertInstanceOf(Category::class, $_category->getObject());
            }
        }

        $collection = $form->get('collection');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection::class,
            ),
        );

        // test for correct extract and populate form values
        // test for collection -> fieldset -> field value
        foreach ($prices as $_k => $_price) {
            self::assertSame(
                $_price,
                $collection->get((string) $_k)
                    ->get('product')
                    ->get('price')
                    ->getValue(),
            );
        }

        // test for collection -> fieldset -> fieldset ->field value
        foreach ($productCountries as $_k => $_countryName) {
            self::assertSame(
                $_countryName,
                $collection->get((string) $_k)
                    ->get('product')
                    ->get('made_in_country')
                    ->get('name')
                    ->getValue(),
            );
        }

        // test collection -> fieldset -> collection -> fieldset -> field value
        foreach ($categoryNames as $_k => $_categoryName) {
            self::assertSame(
                $_categoryName,
                $collection->get((string) $_k)
                    ->get('product')
                    ->get('categories')->get('0')
                    ->get('name')->getValue(),
            );
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \DomainException
     */
    #[Group('test-extract')]
    public function testExtractFromTraversableImplementingToArrayThroughCollectionHydrator(): void
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

        $mockHydrator = $this->createMock(HydratorInterface::class);
        $mockHydrator->expects(self::exactly(2))
            ->method('extract')
            ->willReturnCallback(
                static fn (ArrayModel $object) => ['bar' => $object->bar, 'foo' => $object->foo, 'foobar' => $object->foobar],
            );

        // this test is using a hydrator set on the collection
        $collection->setHydrator($mockHydrator);

        $this->prepareForExtractWithCustomTraversable($collection);

        $expected = [
            ['bar' => 'bar_value_1', 'foo' => 'foo_value_1', 'foobar' => 'foobar_value_1'],
            ['bar' => 'bar_value_2', 'foo' => 'foo_value_2', 'foobar' => 'foobar_value_2'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \DomainException
     */
    #[Group('test-extract')]
    public function testExtractFromTraversableImplementingToArrayThroughTargetElementHydrator(): void
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

        $mockHydrator = $this->createMock(HydratorInterface::class);
        $mockHydrator->expects(self::exactly(2))
            ->method('extract')
            ->willReturnCallback(
                static fn (ArrayModel $object) => ['bar' => $object->bar, 'foo' => $object->foo, 'foobar' => $object->foobar],
            );

        $targetElement->setHydrator($mockHydrator);
        $obj1 = new ArrayModel();
        $targetElement->setObject($obj1);

        $this->prepareForExtractWithCustomTraversable($collection);

        $expected = [
            ['bar' => 'bar_value_1', 'foo' => 'foo_value_1', 'foobar' => 'foobar_value_1'],
            ['bar' => 'bar_value_2', 'foo' => 'foo_value_2', 'foobar' => 'foobar_value_2'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractMaintainsTargetElementObject2(): void
    {
        $color1 = $this->createMock(Element\Color::class);
        $color1->expects(self::never())
            ->method('getName');

        $color2 = $this->createMock(Element\Color::class);
        $color2->expects(self::never())
            ->method('getName');

        $arrayCollection = [
            'color1' => $color1,
            'color2' => $color2,
        ];

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

        $collection->setObject($arrayCollection);

        self::assertSame($arrayCollection, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractMaintainsTargetElementObject3(): void
    {
        $color1 = $this->createMock(Element\Color::class);
        $color1->expects(self::never())
            ->method('getName');
        $color1->expects(self::never())
            ->method('setValue');

        $color2 = $this->createMock(Element\Color::class);
        $color2->expects(self::never())
            ->method('getName');
        $color2->expects(self::never())
            ->method('setValue');

        $color3 = $this->createMock(Element\Color::class);
        $color3->expects(self::once())
            ->method('getName')
            ->willReturn('color1');
        $color3->expects(self::once())
            ->method('setValue')
            ->with($color1);

        $arrayCollection = [
            'color1' => $color1,
            'color2' => $color2,
        ];

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

        $collection->add($color3);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame($arrayCollection, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractMaintainsTargetElementObject4(): void
    {
        $color1 = $this->createMock(Element\Color::class);
        $color1->expects(self::never())
            ->method('getName');
        $color1->expects(self::never())
            ->method('setValue');

        $color2 = $this->createMock(Element\Color::class);
        $color2->expects(self::never())
            ->method('getName');
        $color2->expects(self::never())
            ->method('setValue');

        $color3 = $this->createMock(Element\Color::class);
        $color3->expects(self::once())
            ->method('getName')
            ->willReturn('color1');
        $color3->expects(self::never())
            ->method('setValue');

        $arrayCollection = [
            'color1' => $color1,
            'color2' => $color2,
        ];

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

        $collection->add($color3);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(true);

        self::assertSame($arrayCollection, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractMaintainsTargetElementObject5(): void
    {
        $color1 = $this->createMock(Element\Color::class);
        $color1->expects(self::never())
            ->method('getName');
        $color1->expects(self::never())
            ->method('setValue');

        $color2 = $this->createMock(Element\Color::class);
        $color2->expects(self::never())
            ->method('getName');
        $color2->expects(self::never())
            ->method('setValue');

        $color3 = $this->createMock(Element\Color::class);
        $color3->expects(self::once())
            ->method('getName')
            ->willReturn('color3');
        $color3->expects(self::never())
            ->method('setValue');

        $arrayCollection = [
            'color1' => $color1,
            'color2' => $color2,
        ];

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

        $collection->add($color3);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame($arrayCollection, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testExtractMaintainsTargetElementObject6(): void
    {
        $color1 = $this->createMock(Element\Color::class);
        $color1->expects(self::never())
            ->method('getName');
        $color1->expects(self::never())
            ->method('setValue');

        $color2 = $this->createMock(Element\Color::class);
        $color2->expects(self::never())
            ->method('getName');
        $color2->expects(self::never())
            ->method('setValue');

        $color3 = $this->createMock(Element\Color::class);
        $color3->expects(self::once())
            ->method('getName')
            ->willReturn('color3');
        $color3->expects(self::never())
            ->method('setValue');

        $arrayCollection = [
            'color1' => $color1,
            'color2' => $color2,
        ];

        $form       = new FormCollection2();
        $collection = $form->get('colors');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection::class,
            ),
        );

        $collection->add($color3);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame($arrayCollection, $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets2(): void
    {
        $mainFieldset2 = $this->createMock(Element\Collection::class);
        $mainFieldset2->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset2->expects(self::never())
            ->method('setObject');
        $mainFieldset2->expects(self::never())
            ->method('extract');

        $mainFieldset1 = $this->createMock(Element\Collection::class);
        $mainFieldset1->expects(self::once())
            ->method('allowObjectBinding')
            ->with($mainFieldset2)
            ->willReturn(false);
        $mainFieldset1->expects(self::never())
            ->method('setObject');
        $mainFieldset1->expects(self::never())
            ->method('extract');

        $arrayCollection = ['fs1' => $mainFieldset2];

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

        $collection->setTargetElement($mainFieldset1);
        $collection->setObject($arrayCollection);

        self::assertSame([], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets3(): void
    {
        $data = ['xyz' => 'abc'];

        $mainFieldset2 = $this->createMock(Element\Collection::class);
        $mainFieldset2->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset2->expects(self::never())
            ->method('setObject');
        $mainFieldset2->expects(self::never())
            ->method('extract');

        $mainFieldset1 = $this->createMock(Element\Collection::class);
        $mainFieldset1->expects(self::once())
            ->method('allowObjectBinding')
            ->with($mainFieldset2)
            ->willReturn(true);
        $mainFieldset1->expects(self::once())
            ->method('setObject')
            ->with($mainFieldset2);
        $mainFieldset1->expects(self::once())
            ->method('extract')
            ->willReturn($data);

        $arrayCollection = ['fs1' => $mainFieldset2];

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

        $collection->setTargetElement($mainFieldset1);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame(['fs1' => $data], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets4(): void
    {
        $data = ['xyz' => 'abc'];

        $mainFieldset2 = $this->createMock(Element\Collection::class);
        $mainFieldset2->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset2->expects(self::never())
            ->method('setObject');
        $mainFieldset2->expects(self::never())
            ->method('extract');

        $mainFieldset3 = $this->createMock(Element\Collection::class);
        $mainFieldset3->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset3->expects(self::once())
            ->method('setObject')
            ->with($mainFieldset2);
        $mainFieldset3->expects(self::never())
            ->method('extract');
        $mainFieldset3->expects(self::once())
            ->method('getName')
            ->willReturn('fs1');

        $mainFieldset1 = $this->createMock(Element\Collection::class);
        $mainFieldset1->expects(self::once())
            ->method('allowObjectBinding')
            ->with($mainFieldset2)
            ->willReturn(true);
        $mainFieldset1->expects(self::once())
            ->method('setObject')
            ->with($mainFieldset2);
        $mainFieldset1->expects(self::once())
            ->method('extract')
            ->willReturn($data);

        $arrayCollection = ['fs1' => $mainFieldset2];

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

        $collection->add($mainFieldset3);
        $collection->setTargetElement($mainFieldset1);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame(['fs1' => $data], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets5(): void
    {
        $data = ['xyz' => 'abc'];

        $mainFieldset2 = $this->createMock(Element\Collection::class);
        $mainFieldset2->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset2->expects(self::never())
            ->method('setObject');
        $mainFieldset2->expects(self::never())
            ->method('extract');

        $mainFieldset3 = $this->createMock(Element\Collection::class);
        $mainFieldset3->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset3->expects(self::never())
            ->method('setObject');
        $mainFieldset3->expects(self::never())
            ->method('extract');
        $mainFieldset3->expects(self::once())
            ->method('getName')
            ->willReturn('fs1');

        $mainFieldset1 = $this->createMock(Element\Collection::class);
        $mainFieldset1->expects(self::once())
            ->method('allowObjectBinding')
            ->with($mainFieldset2)
            ->willReturn(true);
        $mainFieldset1->expects(self::once())
            ->method('setObject')
            ->with($mainFieldset2);
        $mainFieldset1->expects(self::once())
            ->method('extract')
            ->willReturn($data);

        $arrayCollection = ['fs1' => $mainFieldset2];

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

        $collection->add($mainFieldset3);
        $collection->setTargetElement($mainFieldset1);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(true);

        self::assertSame(['fs1' => $data], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets6(): void
    {
        $data = ['xyz' => 'abc'];

        $mainFieldset2 = $this->createMock(Element\Collection::class);
        $mainFieldset2->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset2->expects(self::never())
            ->method('setObject');
        $mainFieldset2->expects(self::never())
            ->method('extract');

        $mainFieldset3 = $this->createMock(Element\Collection::class);
        $mainFieldset3->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset3->expects(self::never())
            ->method('setObject');
        $mainFieldset3->expects(self::never())
            ->method('extract');
        $mainFieldset3->expects(self::once())
            ->method('getName')
            ->willReturn('fs3');

        $mainFieldset1 = $this->createMock(Element\Collection::class);
        $mainFieldset1->expects(self::once())
            ->method('allowObjectBinding')
            ->with($mainFieldset2)
            ->willReturn(true);
        $mainFieldset1->expects(self::once())
            ->method('setObject')
            ->with($mainFieldset2);
        $mainFieldset1->expects(self::once())
            ->method('extract')
            ->willReturn($data);

        $arrayCollection = ['fs1' => $mainFieldset2];

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

        $collection->add($mainFieldset3);
        $collection->setTargetElement($mainFieldset1);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame(['fs1' => $data], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('test-extract')]
    public function testCollectionCanBindObjectAndPopulateAndExtractNestedFieldsets7(): void
    {
        $data = ['xyz' => 'abc'];

        $mainFieldset2 = $this->createMock(Element\Collection::class);
        $mainFieldset2->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset2->expects(self::never())
            ->method('setObject');
        $mainFieldset2->expects(self::never())
            ->method('extract');

        $mainFieldset3 = $this->createMock(Element\Collection::class);
        $mainFieldset3->expects(self::never())
            ->method('allowObjectBinding');
        $mainFieldset3->expects(self::never())
            ->method('setObject');
        $mainFieldset3->expects(self::never())
            ->method('extract');
        $mainFieldset3->expects(self::never())
            ->method('getName');

        $mainFieldset1 = $this->createMock(Element\Collection::class);
        $matcher       = self::exactly(2);
        $mainFieldset1->expects($matcher)
            ->method('allowObjectBinding')
            ->with($mainFieldset2)
            ->willReturnCallback(
                /** @throws void */
                static function (Element\Collection $object) use ($matcher, $mainFieldset2, $mainFieldset3): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame($object, $mainFieldset2),
                        default => self::assertSame($object, $mainFieldset3),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        1 => false,
                        default => true,
                    };
                },
            );
        $mainFieldset1->expects(self::once())
            ->method('setObject')
            ->with($mainFieldset2);
        $mainFieldset1->expects(self::once())
            ->method('extract')
            ->willReturn($data);

        $arrayCollection = [
            'fs2' => $mainFieldset2,
            'fs3' => $mainFieldset3,
        ];

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

        $collection->setTargetElement($mainFieldset1);
        $collection->setObject($arrayCollection);
        $collection->setCreateNewObjects(false);

        self::assertSame(['fs3' => $data], $collection->extract());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testPopulateValuesWithFirstKeyGreaterThanZero(): void
    {
        $inputData = [
            1 => ['name' => 'black'],
            5 => ['name' => 'white'],
        ];

        // Standalone Collection element
        $collection = new ElementGroup(
            'fieldsets',
            [
                'count' => 1,
                'target_element' => new CategoryFieldset(),
            ],
        );

        $form = new Form();
        $form->add(
            [
                'name' => 'collection',
                'options' => [
                    'count' => 1,
                    'target_element' => new CategoryFieldset(),
                ],
                'type' => ElementGroup::class,
            ],
        );

        // Collection element attached to a form
        $formCollection = $form->get('collection');
        assert(
            $formCollection instanceof ElementGroup,
            sprintf(
                '$formCollection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $formCollection::class,
            ),
        );

        $collection->populateValues($inputData);
        $formCollection->populateValues($inputData);

        self::assertCount(count($collection->getFieldsets()), $inputData);
        self::assertCount(count($formCollection->getFieldsets()), $inputData);

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(5, $collLastIndex->getValue($collection));

        $formcollLastIndex = new ReflectionProperty($formCollection, 'lastChildIndex');
        self::assertSame(5, $formcollLastIndex->getValue($formCollection));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testPopulateValuesWithFirstKeyGreaterThanZero2(): void
    {
        $inputData = [
            0 => ['name' => 'green'],
            7 => ['name' => 'red'],
            1 => ['name' => 'black'],
            5 => ['name' => 'white'],
        ];

        // Standalone Collection element
        $collection = new ElementGroup(
            'fieldsets',
            [
                'count' => 1,
                'target_element' => new CategoryFieldset(),
            ],
        );

        $form = new Form();
        $form->add(
            [
                'name' => 'collection',
                'options' => [
                    'count' => 1,
                    'target_element' => new CategoryFieldset(),
                ],
                'type' => ElementGroup::class,
            ],
        );

        // Collection element attached to a form
        $formCollection = $form->get('collection');
        assert(
            $formCollection instanceof ElementGroup,
            sprintf(
                '$formCollection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $formCollection::class,
            ),
        );

        $collection->populateValues($inputData);
        $formCollection->populateValues($inputData);

        self::assertCount(count($collection->getFieldsets()), $inputData);
        self::assertCount(count($formCollection->getFieldsets()), $inputData);

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(7, $collLastIndex->getValue($collection));

        $formcollLastIndex = new ReflectionProperty($formCollection, 'lastChildIndex');
        self::assertSame(7, $formcollLastIndex->getValue($formCollection));
    }

    /**
     * @throws Exception
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    #[Group('test-populate-values')]
    public function testCanRemoveAllElementsIfAllowRemoveIsTrue(): void
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

        // By default, $collection contains 2 elements
        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));

        $collection->populateValues([]);
        self::assertCount(0, $collection->getElements());

        $collLastIndex = new ReflectionProperty($collection, 'lastChildIndex');
        self::assertSame(1, $collLastIndex->getValue($collection));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanBindObjectMultipleNestedFieldsets(): void
    {
        $products        = [];
        $shop            = [];
        $productFieldset = new ProductFieldset();
        $productFieldset->setHydrator(new ArraySerializableHydrator());
        $productFieldset->setObject(new Product());

        $nestedFieldset = new Fieldset('nested');
        $nestedFieldset->setHydrator(new ObjectPropertyHydrator());
        $nestedFieldset->setObject(new stdClass());
        $nestedFieldset->add(
            [
                'name' => 'products',
                'options' => [
                    'count' => 2,
                    'target_element' => $productFieldset,
                ],
                'type' => ElementGroup::class,
            ],
        );

        $mainFieldset = new Fieldset('main');
        $mainFieldset->setUseAsBaseFieldset(true);
        $mainFieldset->setHydrator(new ObjectPropertyHydrator());
        $mainFieldset->setObject(new stdClass());
        $mainFieldset->add(
            [
                'name' => 'nested',
                'options' => [
                    'count' => 2,
                    'target_element' => $nestedFieldset,
                ],
                'type' => ElementGroup::class,
            ],
        );

        $form = new Form();
        $form->setHydrator(new ObjectPropertyHydrator());
        $form->add($mainFieldset);

        $market = new stdClass();

        $prices = [100, 200];

        $products[0] = new Product();
        $products[0]->setPrice($prices[0]);
        $products[1] = new Product();
        $products[1]->setPrice($prices[1]);

        $shop[0]           = new stdClass();
        $shop[0]->products = $products;

        $shop[1]           = new stdClass();
        $shop[1]->products = $products;

        $market->nested = $shop;
        $form->bind($market);

        // test for object binding

        $collection1 = $form->get('main');

        assert(
            $collection1 instanceof Fieldset,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                Fieldset::class,
                $collection1::class,
            ),
        );

        // Main fieldset has a collection 'nested'...
        self::assertCount(1, $collection1->getFieldsets());

        foreach ($collection1->getFieldsets() as $_fieldset) {
            // ...which contains two stdClass objects (shops)
            self::assertCount(2, $_fieldset->getFieldsets());

            foreach ($_fieldset->getFieldsets() as $_nestedfieldset) {
                // Each shop is represented by a single fieldset
                self::assertCount(1, $_nestedfieldset->getFieldsets());

                foreach ($_nestedfieldset->getFieldsets() as $_productfieldset) {
                    // Each shop fieldset contain a collection with two products in it
                    self::assertCount(2, $_productfieldset->getFieldsets());

                    foreach ($_productfieldset->getFieldsets() as $_product) {
                        self::assertInstanceOf(Product::class, $_product->getObject());
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testNestedCollections(): void
    {
        /** @see https://github.com/zendframework/zf2/issues/5640 */
        $addressesFieldeset = new AddressFieldset();
        $addressesFieldeset->setHydrator(new ClassMethodsHydrator());

        $form = new Form();
        $form->setHydrator(new ObjectPropertyHydrator());
        $form->add(
            [
                'name' => 'addresses',
                'options' => [
                    'count' => 2,
                    'target_element' => $addressesFieldeset,
                ],
                'type' => ElementGroup::class,
            ],
        );

        $data = [
            ['number' => '0000000001', 'street' => 'street1'],
            ['number' => '0000000002', 'street' => 'street2'],
        ];

        $phone1 = new Phone();
        $phone1->setNumber($data[0]['number']);

        $phone2 = new Phone();
        $phone2->setNumber($data[1]['number']);

        $address1 = new Address();
        $address1->setStreet($data[0]['street']);
        $address1->setPhones([$phone1]);

        $address2 = new Address();
        $address2->setStreet($data[1]['street']);
        $address2->setPhones([$phone2]);

        $customer            = new stdClass();
        $customer->addresses = [$address1, $address2];

        $form->bind($customer);

        $collection1 = $form->get('addresses');

        assert(
            $collection1 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection1::class,
            ),
        );

        // test for object binding
        foreach ($collection1->getFieldsets() as $_fieldset) {
            self::assertInstanceOf(Address::class, $_fieldset->getObject());

            foreach ($_fieldset->getFieldsets() as $_childFieldsetName => $_childFieldset) {
                switch ($_childFieldsetName) {
                    case 'city':
                        self::assertInstanceOf(City::class, $_childFieldset->getObject());

                        break;
                    case 'phones':
                        foreach ($_childFieldset->getFieldsets() as $_phoneFieldset) {
                            self::assertInstanceOf(
                                Phone::class,
                                $_phoneFieldset->getObject(),
                            );
                        }

                        break;
                }
            }
        }

        // test for correct extract and populate
        $index = 0;

        $collection2 = $form->get('addresses');

        assert(
            $collection2 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection2::class,
            ),
        );

        foreach ($collection2 as $_addresses) {
            self::assertSame($data[$index]['street'], $_addresses->get('street')->getValue());

            // assuming data has just 1 phone entry
            foreach ($_addresses->get('phones') as $phone) {
                self::assertSame($data[$index]['number'], $phone->get('number')->getValue());
            }

            ++$index;
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetDataOnFormPopulatesCollection(): void
    {
        $form = new Form();
        $form->add(
            [
                'name' => 'names',
                'options' => [
                    'target_element' => new Element\Text(),
                ],
                'type' => ElementGroup::class,
            ],
        );

        $names = ['foo', 'bar', 'baz', 'bat'];

        $form->setData(
            ['names' => $names],
        );

        self::assertCount(count($names), $form->get('names'));

        $i          = 0;
        $collection = $form->get('names');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection::class,
            ),
        );

        foreach ($collection as $field) {
            self::assertSame($names[$i], $field->getValue());
            ++$i;
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('prepare-count')]
    public function testSettingSomeDataButNoneForCollectionReturnsSpecifiedNumberOfElementsAfterPrepare(): void
    {
        $count = 2;

        $form = new Form();
        $form->add(new Element\Text('input'));
        $form->add(
            [
                'name' => 'names',
                'options' => [
                    'count' => $count,
                    'target_element' => new Element\Text(),
                ],
                'type' => ElementGroup::class,
            ],
        );

        $form->setData(
            ['input' => 'foo'],
        );

        $collection1 = $form->get('names');

        assert(
            $collection1 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection1::class,
            ),
        );

        self::assertCount(0, $collection1);

        $form->prepare();

        $collection2 = $form->get('names');

        assert(
            $collection2 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection2::class,
            ),
        );

        self::assertCount($count, $collection2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('prepare-count')]
    public function testSettingSomeDataButNoneForCollectionReturnsSpecifiedNumberOfElementsAfterPrepare2(): void
    {
        $count = 0;

        $form = new Form();
        $form->add(new Element\Text('input'));
        $form->add(
            [
                'name' => 'names',
                'options' => [
                    'count' => $count,
                    'target_element' => new Element\Text(),
                ],
                'type' => ElementGroup::class,
            ],
        );

        $form->setData(
            ['input' => 'foo'],
        );

        $collection1 = $form->get('names');

        assert(
            $collection1 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection1::class,
            ),
        );

        self::assertCount(0, $collection1);

        $form->prepare();

        $collection2 = $form->get('names');

        assert(
            $collection2 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection2::class,
            ),
        );

        self::assertCount($count, $collection2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Group('prepare-count')]
    public function testSettingSomeDataButNoneForCollectionReturnsSpecifiedNumberOfElementsAfterPrepare3(): void
    {
        $count = 1;

        $form = new Form();
        $form->add(new Element\Text('input'));
        $form->add(
            [
                'name' => 'names',
                'options' => [
                    'count' => $count,
                    'target_element' => new Element\Text(),
                ],
                'type' => ElementGroup::class,
            ],
        );

        $form->setData(
            ['input' => 'foo'],
        );

        $collection1 = $form->get('names');

        assert(
            $collection1 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection1::class,
            ),
        );

        self::assertCount(0, $collection1);

        $form->prepare();

        $collection2 = $form->get('names');

        assert(
            $collection2 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                $collection2::class,
            ),
        );

        self::assertCount($count, $collection2);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testMininumLenghtIsMaintanedWhenSettingASmallerCollection(): void
    {
        $arrayCollection = [
            new Element\Color(),
            new Element\Color(),
        ];

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

        $collection->setCount(3);
        $collection->setObject($arrayCollection);
        self::assertSame(3, $collection->getCount());
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    #[Group('issue-6263')]
    #[Group('issue-6518')]
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface(): void
    {
        $count = 2;
        $form  = new Form('test');
        $text  = new Element\Text('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => $count,
                    'target_element' => $text,
                ],
                'type' => ElementGroup::class,
            ],
        );
        $object = new ArrayObject(['text' => ['Foo', 'Bar']]);
        $form->bind($object);
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertIsArray($result['text']);
        self::assertCount($count, $result['text']);
        self::assertArrayHasKey(0, $result['text']);
        self::assertSame('Foo', $result['text'][0]);
        self::assertArrayHasKey(1, $result['text']);
        self::assertSame('Bar', $result['text'][1]);

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
        self::assertCount($count, $elements2);
        self::assertContainsOnlyInstancesOf(Element\Text::class, $elements2);

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
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface2(): void
    {
        $count = 2;
        $form  = new Form('test');
        $text  = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'options' => [
                    'count' => $count,
                    'target_element' => $text,
                ],
                'type' => ElementGroup::class,
            ],
        );
        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
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

    /** @throws InvalidArgumentException */
    private function prepareForExtract(ElementGroup $collection): void
    {
        $targetElement = $collection->getTargetElement();
        assert(
            $targetElement instanceof FieldsetInterface,
            sprintf(
                '$targetElement should be an Instance of %s, but was %s',
                FieldsetInterface::class,
                get_debug_type($targetElement),
            ),
        );

        $obj1 = new stdClass();

        $targetElement
            ->setHydrator(new ObjectPropertyHydrator())
            ->setObject($obj1);

        $obj2        = new stdClass();
        $obj2->field = 'fieldOne';

        $obj3        = new stdClass();
        $obj3->field = 'fieldTwo';

        $collection->setObject(
            [
                'obj2' => $obj2,
                'obj3' => $obj3,
            ],
        );
    }

    /** @throws \DomainException */
    private function prepareForExtractWithCustomTraversable(FieldsetInterface $collection): void
    {
        $obj2 = new ArrayModel();
        $obj2->exchangeArray(
            ['bar' => 'bar_value_1', 'foo' => 'foo_value_1', 'foobar' => 'foobar_value_1'],
        );
        $obj3 = new ArrayModel();
        $obj3->exchangeArray(
            ['bar' => 'bar_value_2', 'foo' => 'foo_value_2', 'foobar' => 'foobar_value_2'],
        );

        $traversable = new CustomCollection();
        $traversable->append($obj2);
        $traversable->append($obj3);
        $collection->setObject($traversable);
    }
}
