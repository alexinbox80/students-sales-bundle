<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Customer;
use alexinbox80\Shared\Domain\Model\OId;

interface CustomersRepositoryInterface
{
    /**
     * @throws NotFoundException
     */
    public function get(OId $id): Customer;

    public function find(OId $id): ?Customer;

    public function add(Customer $customer): void;
}
