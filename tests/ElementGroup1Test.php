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

use Laminas\Form\Element;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Form;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Mimmi20\Form\Element\Group\ElementGroup;
use Mimmi20Test\Form\Element\Group\TestAsset\AddressFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\CustomTraversable;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Address;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Category;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Phone;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Product;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection;
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
use function iterator_count;
use function spl_object_hash;
use function sprintf;

final class ElementGroup1Test extends TestCase
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
            'Laminas\Form\Element\Collection::setObject expects an array or Traversable object argument; received "NULL"',
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
            'Laminas\Form\Element\Collection::setTargetElement requires that $elementOrFieldset be an object implementing Laminas\Form\Element\ElementInterface; received "NULL"',
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
}
