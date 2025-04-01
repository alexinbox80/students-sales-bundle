<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\Model;

use alexinbox80\StudentsSalesBundle\Domain\Model\Product;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Product::class)]
class ProductTest extends TestCase
{
    private Product $product;
    private Price $price;
    private Currency $currency;

    protected function setUp(): void
    {
        $this->currency = Currency::USD;
        $this->price = new Price(1000, $this->currency);
        $this->product = Product::create('Test Product', $this->price);
    }

    public function testCreateProductGeneratesId(): void
    {
        self::assertNotNull($this->product->getId());
    }

    public function testProductHasCorrectName(): void
    {
        self::assertSame('Test Product', $this->product->getName());
    }

    public function testProductHasCorrectPrice(): void
    {
        self::assertTrue($this->product->getPrice()->isEqual($this->price));
    }

    public function testUpdateName(): void
    {
        $this->product->updateName('New Name');
        self::assertSame('New Name', $this->product->getName());
    }

    public function testUpdatePrice(): void
    {
        $newPrice = new Price(2000, Currency::EUR);
        $this->product->updatePrice($newPrice);
        self::assertTrue($this->product->getPrice()->isEqual($newPrice));
    }
}
