<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Customer;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @extends AbstractRepository<Customer>
 */
class CustomerRepository extends AbstractRepository
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);
    }

    public function find(OId $customerId): ?Customer
    {
        $repository = $this->entityManager->getRepository(Customer::class);
        /** @var Customer|null $customer */
        $customer = $repository->find($customerId);

        return $customer;
    }

    public function add(Customer $customer): void
    {

    }
}
