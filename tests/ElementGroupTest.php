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

namespace Mimmi20Test\Form\Element;

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
use Laminas\Hydrator\ObjectPropertyHydrator;
use Laminas\InputFilter\ArrayInput;
use Mimmi20\Form\Element\ElementGroup;
use Mimmi20Test\Form\Element\TestAsset\AddressFieldset;
use Mimmi20Test\Form\Element\TestAsset\ArrayModel;
use Mimmi20Test\Form\Element\TestAsset\CategoryFieldset;
use Mimmi20Test\Form\Element\TestAsset\CustomCollection;
use Mimmi20Test\Form\Element\TestAsset\CustomTraversable;
use Mimmi20Test\Form\Element\TestAsset\Entity\Address;
use Mimmi20Test\Form\Element\TestAsset\Entity\Category;
use Mimmi20Test\Form\Element\TestAsset\Entity\Country;
use Mimmi20Test\Form\Element\TestAsset\Entity\Phone;
use Mimmi20Test\Form\Element\TestAsset\Entity\Product;
use Mimmi20Test\Form\Element\TestAsset\FormCollection;
use Mimmi20Test\Form\Element\TestAsset\PhoneFieldset;
use Mimmi20Test\Form\Element\TestAsset\ProductFieldset;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

use function assert;
use function count;
use function extension_loaded;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function iterator_count;
use function spl_object_hash;
use function sprintf;

final class ElementGroupTest extends TestCase
{
    private FormCollection $form;
    private ProductFieldset $productFieldset;

    /**
     * @throws InvalidArgumentException
     */
    protected function setUp(): void
    {
        $this->form            = new FormCollection();
        $this->productFieldset = new ProductFieldset();

        parent::setUp();
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    public function testCanRetrieveDefaultPlaceholder(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $placeholder = $collection->getTemplatePlaceholder();
        self::assertSame('__index__', $placeholder);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCannotAllowNewElementsIfAllowAddIsFalse(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
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

        $this->expectException(DomainException::class);
        $data[] = 'orange';
        $collection->populateValues($data);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanAddNewElementsIfAllowAddIsTrue(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowAdd(true);
        self::assertTrue($collection->allowAdd());

        // By default, $collection contains 2 elements
        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $data[] = 'orange';
        $collection->populateValues($data);
        self::assertCount(3, $collection->getElements());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanRemoveElementsIfAllowRemoveIsTrue(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowRemove(true);
        self::assertTrue($collection->allowRemove());

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        unset($data[0]);

        $collection->populateValues($data);
        self::assertCount(1, $collection->getElements());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanReplaceElementsIfAllowAddAndAllowRemoveIsTrue(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowAdd(true);
        $collection->setAllowRemove(true);

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        unset($data[0]);
        $data[] = 'orange';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanValidateFormWithCollectionWithoutTemplate(): void
    {
        $this->form->setData(
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
            ]
        );

        self::assertTrue($this->form->isValid());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCannotValidateFormWithCollectionWithBadColor(): void
    {
        $this->form->setData(
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
            ]
        );

        self::assertFalse($this->form->isValid());
        $messages = $this->form->getMessages();

        assert(is_array($messages));

        self::assertArrayHasKey('colors', $messages);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCannotValidateFormWithCollectionWithBadFieldsetField(): void
    {
        $this->form->setData(
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
            ]
        );

        self::assertFalse($this->form->isValid());
        $messages = $this->form->getMessages();

        assert(is_array($messages));

        self::assertCount(1, $messages);
        self::assertArrayHasKey('fieldsets', $messages);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanValidateFormWithCollectionWithTemplate(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        self::assertFalse($collection->shouldCreateTemplate());
        $collection->setShouldCreateTemplate(true);
        self::assertTrue($collection->shouldCreateTemplate());

        $collection->setTemplatePlaceholder('__template__');

        $this->form->setData(
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
            ]
        );

        self::assertTrue($this->form->isValid());
    }

    /**
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testThrowExceptionIfThereAreLessElementsAndAllowRemoveNotAllowed(): void
    {
        $this->expectException(DomainException::class);

        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowRemove(false);

        $this->form->setData(
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
            ]
        );

        $this->form->isValid();
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanValidateLessThanSpecifiedCount(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowRemove(true);

        $this->form->setData(
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
            ]
        );

        self::assertTrue($this->form->isValid());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetOptions(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $element = new Element('foo');
        $collection->setOptions(
            [
                'target_element' => $element,
                'count' => 2,
                'allow_add' => true,
                'allow_remove' => false,
                'should_create_template' => true,
                'template_placeholder' => 'foo',
            ]
        );

        self::assertInstanceOf('Laminas\Form\Element', $collection->getOption('target_element'));
        self::assertSame(2, $collection->getOption('count'));
        self::assertTrue($collection->getOption('allow_add'));
        self::assertFalse($collection->getOption('allow_remove'));
        self::assertTrue($collection->getOption('should_create_template'));
        self::assertSame('foo', $collection->getOption('template_placeholder'));
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetOptionsTraversable(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $element = new Element('foo');
        $collection->setOptions(
            new CustomTraversable(
                [
                    'target_element' => $element,
                    'count' => 2,
                    'allow_add' => true,
                    'allow_remove' => false,
                    'should_create_template' => true,
                    'template_placeholder' => 'foo',
                ]
            )
        );

        self::assertInstanceOf('Laminas\Form\Element', $collection->getOption('target_element'));
        self::assertSame(2, $collection->getOption('count'));
        self::assertTrue($collection->getOption('allow_add'));
        self::assertFalse($collection->getOption('allow_remove'));
        self::assertTrue($collection->getOption('should_create_template'));
        self::assertSame('foo', $collection->getOption('template_placeholder'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetObjectNullRaisesException(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $this->expectException(InvalidArgumentException::class);
        $collection->setObject(null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetTargetElementNullRaisesException(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $this->expectException(InvalidArgumentException::class);
        $collection->setTargetElement(null);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testGetTargetElement(): void
    {
        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $element = new Element('foo');
        $collection->setTargetElement($element);

        self::assertInstanceOf('Laminas\Form\Element', $collection->getTargetElement());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testExtractFromObjectDoesntTouchOriginalObject(): void
    {
        $form = new Form();
        $form->setHydrator(new ClassMethodsHydrator());
        $this->productFieldset->setUseAsBaseFieldset(true);
        $form->add($this->productFieldset);

        $collection = $this->productFieldset->get('categories');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $originalObjectHash = spl_object_hash(
            $collection->getTargetElement()->getObject()
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
                    'name' => 'franz',
                    'price' => 13,
                    'categories' => [
                        ['name' => 'sepp'],
                        ['name' => 'herbert'],
                    ],
                ],
            ]
        );

        $objectAfterExtractHash = spl_object_hash(
            $collection->getTargetElement()->getObject()
        );

        self::assertSame($originalObjectHash, $objectAfterExtractHash);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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
        $this->productFieldset->setUseAsBaseFieldset(true);
        $form->add($this->productFieldset);

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
                    'name' => 'franz',
                    'price' => 13,
                    'categories' => [
                        ['name' => 'sepp'],
                        ['name' => 'herbert'],
                    ],
                ],
            ]
        );
        $form->isValid();

        $categories = $product->getCategories();
        self::assertSame($categories[0], $cat1);
        self::assertSame($categories[1], $cat2);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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

        $this->productFieldset->setUseAsBaseFieldset(true);
        $categories = $this->productFieldset->get('categories');
        $categories->setOptions(
            ['create_new_objects' => true]
        );

        $form = new Form();
        $form->setHydrator(new ClassMethodsHydrator());
        $form->add($this->productFieldset);

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
                    'name' => 'franz',
                    'price' => 13,
                    'categories' => [
                        ['name' => 'sepp'],
                        ['name' => 'herbert'],
                    ],
                ],
            ]
        );
        $form->isValid();

        $categories = $product->getCategories();
        self::assertNotSame($categories[0], $cat1);
        self::assertNotSame($categories[1], $cat2);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6585
     * @group issue-6614
     */
    public function testAddingCollectionElementAfterBind(): void
    {
        $form = new Form();
        $form->setHydrator(new ObjectPropertyHydrator());

        $phone = new PhoneFieldset();

        $form->add(
            [
                'name' => 'phones',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $phone,
                    'count' => 1,
                    'allow_add' => true,
                ],
            ]
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6585
     * @group issue-6614
     */
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
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $addressesFieldset,
                    'count' => 1,
                ],
            ]
        );

        $data = [
            'addresses' => [
                [
                    'street' => 'street1',
                    'phones' => [
                        ['number' => '1234567'],
                    ],
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testDoNotCreateExtraFieldsetOnMultipleBind(): void
    {
        $form = new Form();
        $this->productFieldset->setHydrator(new ClassMethodsHydrator());
        $form->add($this->productFieldset);
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
        self::assertSame(count($categories), iterator_count($form->get('product')->get('categories')->getIterator()));

        // this won't pass, but must
        $form->bind($market);
        self::assertSame(count($categories), iterator_count($form->get('product')->get('categories')->getIterator()));
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractDefaultIsEmptyArray(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        self::assertSame([], $collection->extract());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractThroughTargetElementHydrator(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $this->prepareForExtract($collection);

        $expected = [
            'obj2' => ['field' => 'fieldOne'],
            'obj3' => ['field' => 'fieldTwo'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractMaintainsTargetElementObject(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $this->prepareForExtract($collection);

        $expected = $collection->getTargetElement()->getObject();

        $collection->extract();

        $test = $collection->getTargetElement()->getObject();

        self::assertSame($expected, $test);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractThroughCustomHydrator(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $this->prepareForExtract($collection);

        $mockHydrator = $this->createMock('Laminas\Hydrator\HydratorInterface');
        $mockHydrator->expects(self::exactly(2))
            ->method('extract')
            ->willReturnCallback(
                static fn ($object) => ['value' => $object->field . '_foo']
            );

        $collection->setHydrator($mockHydrator);

        $expected = [
            'obj2' => ['value' => 'fieldOne_foo'],
            'obj3' => ['value' => 'fieldTwo_foo'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractFromTraversable(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $this->prepareForExtract($collection);

        $traversable = new ArrayObject($collection->getObject());
        $collection->setObject($traversable);

        $expected = [
            'obj2' => ['field' => 'fieldOne'],
            'obj3' => ['field' => 'fieldTwo'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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
            ]
        );

        $myForm = new Form();
        $myForm->add(
            [
                'name' => 'collection',
                'type' => ElementGroup::class,
                'options' => ['target_element' => $myFieldset],
            ]
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $mainFieldset,
                    'count' => 2,
                ],
            ]
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

        //test for object binding
        $_marketCollection = $form->get('collection');
        self::assertInstanceOf(ElementGroup::class, $_marketCollection);

        foreach ($_marketCollection as $_shopFieldset) {
            self::assertInstanceOf('Laminas\Form\Fieldset', $_shopFieldset);
            self::assertInstanceOf('stdClass', $_shopFieldset->getObject());

            // test for collection -> fieldset
            $_productFieldset = $_shopFieldset->get('product');
            self::assertInstanceOf('Mimmi20Test\Form\Element\TestAsset\ProductFieldset', $_productFieldset);
            self::assertInstanceOf('Mimmi20Test\Form\Element\TestAsset\Entity\Product', $_productFieldset->getObject());

            // test for collection -> fieldset -> fieldset
            self::assertInstanceOf(
                'Mimmi20Test\Form\Element\TestAsset\CountryFieldset',
                $_productFieldset->get('made_in_country')
            );
            self::assertInstanceOf(
                'Mimmi20Test\Form\Element\TestAsset\Entity\Country',
                $_productFieldset->get('made_in_country')->getObject()
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
                get_class($collection)
            )
        );

        // test for correct extract and populate form values
        // test for collection -> fieldset -> field value
        foreach ($prices as $_k => $_price) {
            self::assertSame(
                $_price,
                $collection->get((string) $_k)
                    ->get('product')
                    ->get('price')
                    ->getValue()
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
                    ->getValue()
            );
        }

        // test collection -> fieldset -> collection -> fieldset -> field value
        foreach ($categoryNames as $_k => $_categoryName) {
            self::assertSame(
                $_categoryName,
                $collection->get((string) $_k)
                    ->get('product')
                    ->get('categories')->get('0')
                    ->get('name')->getValue()
            );
        }
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractFromTraversableImplementingToArrayThroughCollectionHydrator(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        // this test is using a hydrator set on the collection
        $collection->setHydrator(new ArraySerializableHydrator());

        $this->prepareForExtractWithCustomTraversable($collection);

        $expected = [
            ['foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1'],
            ['foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     */
    public function testExtractFromTraversableImplementingToArrayThroughTargetElementHydrator(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        // this test is using a hydrator set on the target element of the collection
        $targetElement = $collection->getTargetElement();
        assert(
            $targetElement instanceof FieldsetInterface,
            sprintf(
                '$targetElement should be an Instance of %s, but was %s',
                FieldsetInterface::class,
                is_object($targetElement) ? get_class($targetElement) : gettype($targetElement)
            )
        );

        $targetElement->setHydrator(new ArraySerializableHydrator());
        $obj1 = new ArrayModel();
        $targetElement->setObject($obj1);

        $this->prepareForExtractWithCustomTraversable($collection);

        $expected = [
            ['foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1'],
            ['foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2'],
        ];

        self::assertSame($expected, $collection->extract());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
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
            ]
        );

        $form = new Form();
        $form->add(
            [
                'type' => ElementGroup::class,
                'name' => 'collection',
                'options' => [
                    'count' => 1,
                    'target_element' => new CategoryFieldset(),
                ],
            ]
        );

        // Collection element attached to a form
        $formCollection = $form->get('collection');
        assert(
            $formCollection instanceof ElementGroup,
            sprintf(
                '$formCollection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($formCollection)
            )
        );

        $collection->populateValues($inputData);
        $formCollection->populateValues($inputData);

        self::assertCount(count($collection->getFieldsets()), $inputData);
        self::assertCount(count($formCollection->getFieldsets()), $inputData);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanRemoveAllElementsIfAllowRemoveIsTrue(): void
    {
        $collection = $this->form->get('colors');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowRemove(true);
        $collection->setCount(0);

        // By default, $collection contains 2 elements
        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';

        $collection->populateValues($data);
        self::assertCount(2, $collection->getElements());

        $collection->populateValues([]);
        self::assertCount(0, $collection->getElements());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testCanBindObjectMultipleNestedFieldsets(): void
    {
        $productFieldset = new ProductFieldset();
        $productFieldset->setHydrator(new ArraySerializableHydrator());
        $productFieldset->setObject(new Product());

        $nestedFieldset = new Fieldset('nested');
        $nestedFieldset->setHydrator(new ObjectPropertyHydrator());
        $nestedFieldset->setObject(new stdClass());
        $nestedFieldset->add(
            [
                'name' => 'products',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $productFieldset,
                    'count' => 2,
                ],
            ]
        );

        $mainFieldset = new Fieldset('main');
        $mainFieldset->setUseAsBaseFieldset(true);
        $mainFieldset->setHydrator(new ObjectPropertyHydrator());
        $mainFieldset->setObject(new stdClass());
        $mainFieldset->add(
            [
                'name' => 'nested',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $nestedFieldset,
                    'count' => 2,
                ],
            ]
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

        //test for object binding

        $collection1 = $form->get('main');

        assert(
            $collection1 instanceof Fieldset,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                Fieldset::class,
                get_class($collection1)
            )
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
                        self::assertInstanceOf('Mimmi20Test\Form\Element\TestAsset\Entity\Product', $_product->getObject());
                    }
                }
            }
        }
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $addressesFieldeset,
                    'count' => 2,
                ],
            ]
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
                get_class($collection1)
            )
        );

        //test for object binding
        foreach ($collection1->getFieldsets() as $_fieldset) {
            self::assertInstanceOf(Address::class, $_fieldset->getObject());
            foreach ($_fieldset->getFieldsets() as $_childFieldsetName => $_childFieldset) {
                switch ($_childFieldsetName) {
                    case 'city':
                        self::assertInstanceOf('Mimmi20Test\Form\Element\TestAsset\Entity\City', $_childFieldset->getObject());
                        break;
                    case 'phones':
                        foreach ($_childFieldset->getFieldsets() as $_phoneFieldset) {
                            self::assertInstanceOf(
                                'Mimmi20Test\Form\Element\TestAsset\Entity\Phone',
                                $_phoneFieldset->getObject()
                            );
                        }

                        break;
                }
            }
        }

        //test for correct extract and populate
        $index = 0;

        $collection2 = $form->get('addresses');

        assert(
            $collection2 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection2)
            )
        );

        foreach ($collection2 as $_addresses) {
            self::assertSame($data[$index]['street'], $_addresses->get('street')->getValue());
            //assuming data has just 1 phone entry
            foreach ($_addresses->get('phones') as $phone) {
                self::assertSame($data[$index]['number'], $phone->get('number')->getValue());
            }

            ++$index;
        }
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSetDataOnFormPopulatesCollection(): void
    {
        $form = new Form();
        $form->add(
            [
                'name' => 'names',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => new Element\Text(),
                ],
            ]
        );

        $names = ['foo', 'bar', 'baz', 'bat'];

        $form->setData(
            ['names' => $names]
        );

        self::assertCount(count($names), $form->get('names'));

        $i          = 0;
        $collection = $form->get('names');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        foreach ($collection as $field) {
            self::assertSame($names[$i], $field->getValue());
            ++$i;
        }
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testSettingSomeDataButNoneForCollectionReturnsSpecifiedNumberOfElementsAfterPrepare(): void
    {
        $form = new Form();
        $form->add(new Element\Text('input'));
        $form->add(
            [
                'name' => 'names',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => new Element\Text(),
                    'count' => 2,
                ],
            ]
        );

        $form->setData(
            ['input' => 'foo']
        );

        $collection1 = $form->get('names');

        assert(
            $collection1 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection1)
            )
        );

        self::assertCount(0, $collection1);

        $form->prepare();

        $collection2 = $form->get('names');

        assert(
            $collection2 instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection2)
            )
        );

        self::assertCount(2, $collection2);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testMininumLenghtIsMaintanedWhenSettingASmallerCollection(): void
    {
        $arrayCollection = [
            new Element\Color(),
            new Element\Color(),
        ];

        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setCount(3);
        $collection->setObject($arrayCollection);
        self::assertSame(3, $collection->getCount());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6263
     * @group issue-6518
     */
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface(): void
    {
        $count = 2;
        $form  = new Form('test');
        $text  = new Element\Text('text');
        $form->add(
            [
                'name' => 'text',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $text,
                    'count' => $count,
                ],
            ]
        );
        $object = new ArrayObject(['text' => ['Foo', 'Bar']]);
        $form->bind($object);
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertArrayHasKey('text', $result);
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6263
     * @group issue-6518
     * @group test-3
     */
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface2(): void
    {
        $count = 2;
        $form  = new Form('test');
        $text  = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $text,
                    'count' => $count,
                ],
            ]
        );
        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertArrayHasKey('text', $result);
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6263
     * @group issue-6518
     * @group test-3
     */
    public function testCollectionProperlyHandlesAddingObjectsOfTypeElementInterface3(): void
    {
        $count = 2;
        $form  = new Form('test');
        $text  = new Element\DateSelect('text');
        $form->add(
            [
                'name' => 'text',
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $text,
                    'count' => $count,
                    'should_create_template' => true,
                    'create_new_objects' => false,
                ],
            ]
        );

        //$form->prepare();
        $object = new ArrayObject(['text' => ['2020-01-01', '2021-01-01']]);
        $form->bind($object);
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf(ArrayAccess::class, $result);
        self::assertArrayHasKey('text', $result);
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
     * Unit test to ensure behavior of extract() method is unaffected by refactor
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6263
     * @group issue-6518
     */
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
                'type' => ElementGroup::class,
                'options' => [
                    'target_element' => $mainFieldset,
                    'count' => 2,
                ],
            ]
        );

        $model             = new stdClass();
        $model->collection = [new ArrayObject(['test' => 'bar']), new stdClass()];

        $form->bind($model);
        self::assertTrue($form->isValid());

        $result = $form->getData();
        self::assertInstanceOf('stdClass', $result);
        self::assertObjectHasAttribute('collection', $result);
        self::assertIsArray($result->collection);
        self::assertCount(1, $result->collection);
        self::assertInstanceOf('ArrayObject', $result->collection[0]);
        self::assertArrayHasKey('test', $result->collection[0]);
        self::assertSame('bar', $result->collection[0]['test']);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     *
     * @group issue-6263
     * @group issue-6298
     */
    public function testCanHydrateObject(): void
    {
        $form   = $this->form;
        $object = new ArrayObject();
        $form->bind($object);
        $data = [
            'colors' => ['#ffffff'],
        ];
        $form->setData($data);
        self::assertTrue($form->isValid());
        self::assertIsArray($object['colors']);
        self::assertCount(1, $object['colors']);
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testCanRemoveMultipleElements(): void
    {
        $collection = $this->form->get('colors');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowRemove(true);
        $collection->setCount(0);

        $data   = [];
        $data[] = 'blue';
        $data[] = 'green';
        $data[] = 'red';

        $collection->populateValues($data);

        $collection->populateValues(['colors' => ['0' => 'blue']]);
        self::assertCount(1, $collection->getElements());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws \Laminas\InputFilter\Exception\InvalidArgumentException
     */
    public function testGetErrorMessagesForInvalidCollectionElements(): void
    {
        // Configure InputFilter
        $inputFilter = $this->form->getInputFilter();
        $inputFilter->add(
            [
                'name' => 'colors',
                'type' => ArrayInput::class,
                'required' => true,
            ]
        );
        $inputFilter->add(
            [
                'name' => 'fieldsets',
                'type' => ArrayInput::class,
                'required' => true,
            ]
        );

        $this->form->setData([]);
        $this->form->isValid();

        self::assertSame(
            [
                'colors' => ['isEmpty' => "Value is required and can't be empty"],
                'fieldsets' => ['isEmpty' => "Value is required and can't be empty"],
            ],
            $this->form->getMessages()
        );
    }

    /**
     * @see https://github.com/zendframework/zend-form/pull/230
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testNullTargetElementShouldResultInEmptyData(): void
    {
        $form = new Form();

        $form->add(
            [
                'type' => ElementGroup::class,
                'name' => 'fieldsets',
                'options' => ['count' => 2],
            ]
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
            $form->getData()
        );
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testPopulateValuesTraversable(): void
    {
        $data = new CustomTraversable(['blue', 'green']);

        $collection = $this->form->get('colors');

        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        $collection->setAllowRemove(false);
        $collection->populateValues($data);

        self::assertCount(2, $collection->getElements());
    }

    /**
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     * @throws \Laminas\Stdlib\Exception\InvalidArgumentException
     * @throws InvalidArgumentException
     * @throws DomainException
     */
    public function testSetObjectTraversable(): void
    {
        $collection = $this->form->get('fieldsets');
        assert(
            $collection instanceof ElementGroup,
            sprintf(
                '$collection should be an Instance of %s, but was %s',
                ElementGroup::class,
                get_class($collection)
            )
        );

        // this test is using a hydrator set on the target element of the collection
        $targetElement = $collection->getTargetElement();
        assert(
            $targetElement instanceof FieldsetInterface,
            sprintf(
                '$targetElement should be an Instance of %s, but was %s',
                FieldsetInterface::class,
                is_object($targetElement) ? get_class($targetElement) : gettype($targetElement)
            )
        );

        $targetElement->setHydrator(new ArraySerializableHydrator());
        $obj1 = new ArrayModel();
        $targetElement->setObject($obj1);

        $obj2 = new ArrayModel();
        $obj2->exchangeArray(['foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1']);
        $obj3 = new ArrayModel();
        $obj3->exchangeArray(['foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2']);

        $collection->setObject(new CustomTraversable([$obj2, $obj3]));

        $expected = [
            ['foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1'],
            ['foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2'],
        ];

        self::assertSame($expected, $collection->extract());
        self::assertSame([$obj2, $obj3], $collection->getObject());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function prepareForExtract(ElementGroup $collection): void
    {
        $targetElement = $collection->getTargetElement();
        assert(
            $targetElement instanceof FieldsetInterface,
            sprintf(
                '$targetElement should be an Instance of %s, but was %s',
                FieldsetInterface::class,
                is_object($targetElement) ? get_class($targetElement) : gettype($targetElement)
            )
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
            ]
        );
    }

    private function prepareForExtractWithCustomTraversable(FieldsetInterface $collection): void
    {
        $obj2 = new ArrayModel();
        $obj2->exchangeArray(['foo' => 'foo_value_1', 'bar' => 'bar_value_1', 'foobar' => 'foobar_value_1']);
        $obj3 = new ArrayModel();
        $obj3->exchangeArray(['foo' => 'foo_value_2', 'bar' => 'bar_value_2', 'foobar' => 'foobar_value_2']);

        $traversable = new CustomCollection();
        $traversable->append($obj2);
        $traversable->append($obj3);
        $collection->setObject($traversable);
    }
}
