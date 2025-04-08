<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Product;
use alexinbox80\Shared\Domain\Model\OId;

interface ProductsRepositoryInterface
{
    /**
     * @throws NotFoundException
     */
    public function get(OId $id): ?Product;

    public function find(OId $id): ?Product;

    public function add(Product $product): void;

    public function update(): void;

    public function remove(Product $product): void;
}
