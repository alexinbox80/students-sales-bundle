<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Product;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\ProductsRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<Product>
 */
class ProductsRepository extends AbstractRepository implements ProductsRepositoryInterface
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);
    }

    public function get(OId $id): Product
    {
        $repository = $this->entityManager->getRepository(Product::class);
        /** @var Product|null $product */
        $product = $repository->find($id);

        return $product;
    }

    public function find(OId $id): ?Product
    {
        return $this->find($id);
    }

    public function add(Product $product): void
    {
        $this->store($product);
    }

    public function update(): void
    {
        $this->flush();
    }

    public function remove(Product $product): void
    {
        $product->setDeletedAt();
        $this->flush();
    }
}
