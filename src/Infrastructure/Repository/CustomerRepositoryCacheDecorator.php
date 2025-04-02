<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\StudentsSalesBundle\Domain\Repositories\CustomersRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Customer;
use alexinbox80\Shared\Domain\Model\OId;

class CustomerRepositoryCacheDecorator implements CustomersRepositoryInterface
{
    public function __construct(
        private readonly CustomerRepository $customerRepository
    ) {
    }

    public function get(OId $id): Customer
    {
        return $this->customerRepository->find($id);
    }

    public function find(OId $id): ?Customer
    {
        return $this->customerRepository->find($id);
    }

    public function add(Customer $customer): void
    {
        $this->customerRepository->add($customer);
    }
}
