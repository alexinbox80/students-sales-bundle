<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\StudentsSalesBundle\Domain\Model\ModelInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Throwable;

/**
 * @template T
 */
abstract class AbstractRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager
    ) {
    }

    protected function flush(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @param T $entity
     */
    protected function store(ModelInterface $entity): string
    {
        $this->entityManager->persist($entity);
        $this->flush();

        return $entity->getId();
    }

    /**
     * @param T $entity
     * @throws ORMException
     */
    public function refresh(ModelInterface $entity): void
    {
        $this->entityManager->refresh($entity);
    }

    public function transactional(callable $callable): void
    {
        try {
            $this->entityManager->getConnection()->beginTransaction();
            $callable();
            $this->entityManager->getConnection()->commit();
        } catch (Throwable $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }
    }
}
