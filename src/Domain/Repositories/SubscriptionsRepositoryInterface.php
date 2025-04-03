<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\Shared\Domain\Model\OId;

interface SubscriptionsRepositoryInterface
{
    /**
     * @throws NotFoundException
     */
    public function get(OId $id): Subscription;

    public function find(OId $id): ?Subscription;

    public function add(Subscription $subscription): void;

    public function hasActiveSubscription(OId $customerId): bool;
}
