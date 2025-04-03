<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @extends AbstractRepository<Subscription>
 */
class SubscriptionsRepository extends AbstractRepository implements SubscriptionsRepositoryInterface
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);
    }

    public function find(OId $id): ?Subscription
    {
        $repository = $this->entityManager->getRepository(Subscription::class);
        /** @var Subscription|null $customer */
        $subscription = $repository->find($id);

        return $subscription;
    }

    public function get(OId $id): Subscription
    {
        return $this->find($id);
    }

    public function add(Subscription $subscription): void
    {
        dump($subscription);
        $this->store($subscription);
    }

    public function remove(Subscription $subscription): void
    {
        $subscription->setDeletedAt();
        $this->flush();
    }

    public function searchSubscriptionByCustomerId(string $customerId): Subscription
    {
        /** @var Subscription|null $subscription */
        return $this->entityManager->getRepository(Subscription::class)->findOneBy(['customerId' => $customerId]);
    }

    public function hasActiveSubscription(OId $customerId): bool
    {
        return false;
    }
}
