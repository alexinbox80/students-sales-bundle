<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Product;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\ProductsRepositoryInterface;
use alexinbox80\Shared\Domain\Model\OId;

final class InMemoryProductsRepository implements ProductsRepositoryInterface
{
    /** @var Product[] */
    public array $storage;

    public function __construct(array|null $storage = null)
    {
        if ($storage !== null) {
            $this->storage = $storage;
            return;
        }

        $this->storage = [
            new Product(
                id: OId::fromString(PrepopulatedTestObjects::PRODUCT_ID),
                name: 'Test Product',
                price: new Price(1000, Currency::USD)
            )
        ];
    }

    public function get(OId $id): Product
    {
        foreach ($this->storage as $product) {
            if ($product->getId()->isEqual($id)) {
                return $product;
            }
        }

        throw new NotFoundException();
    }
}
