<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Customer;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\CustomersRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<Customer>
 */
class CustomersRepository extends AbstractRepository implements CustomersRepositoryInterface
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);
    }

    public function get(OId $id): ?Customer
    {
        $repository = $this->entityManager->getRepository(Customer::class);

        /** @var Customer|null $customer */
        $customer =  $repository->findBy(['customerOid' => $id]);

        return $customer[0] ?? null;
    }

    public function find(OId $id): ?Customer
    {
        return $this->find($id);
    }

    public function add(Customer $customer): void
    {
        $this->store($customer);
    }

    public function update(): void
    {
        $this->flush();
    }

    public function remove(Customer $customer): void
    {
        $customer->setDeletedAt();
        $this->flush();
    }
}
