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

use ArrayObject;
use Laminas\Form\Element;
use Laminas\Form\Exception\DomainException;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Form\Fieldset;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Mimmi20\Form\Element\Group\ElementGroup;
use Mimmi20Test\Form\Element\Group\TestAsset\ArrayModel;
use Mimmi20Test\Form\Element\Group\TestAsset\CategoryFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\CountryFieldset;
use Mimmi20Test\Form\Element\Group\TestAsset\CustomCollection;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Category;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Country;
use Mimmi20Test\Form\Element\Group\TestAsset\Entity\Product;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection;
use Mimmi20Test\Form\Element\Group\TestAsset\FormCollection2;
use Mimmi20Test\Form\Element\Group\TestAsset\ProductFieldset;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

use function assert;
use function get_debug_type;
use function is_array;
use function sprintf;

final class ElementGroup2Test extends TestCase
{
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
                static fn ($object) => ['value' => $object->field . '_foo'],
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
                static fn ($object) => ['bar' => $object->bar, 'foo' => $object->foo, 'foobar' => $object->foobar],
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
                static fn ($object) => ['bar' => $object->bar, 'foo' => $object->foo, 'foobar' => $object->foobar],
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
