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
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Mimmi20\Form\Element\Group\ElementGroup;
use Mimmi20Test\Form\Element\Group\TestAsset\AddressFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\CategoryFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Address;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\City;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Phone;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Product;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection;
use Mimmi20Test\Form\Element\Group\TestAsset\ProductFieldset;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;
use stdClass;

use function assert;
use function count;
use function sprintf;

final class ElementGroup3Test extends TestCase
{
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
}
