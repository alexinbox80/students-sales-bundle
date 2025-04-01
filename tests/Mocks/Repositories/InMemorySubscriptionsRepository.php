<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Status;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use alexinbox80\Shared\Domain\Model\OId;
use DateTimeImmutable;

final class InMemorySubscriptionsRepository implements SubscriptionsRepositoryInterface
{
    /** @var Subscription[] */
    public array $storage;

    /**
     * @param Subscription[]|null $storage
     */
    public function __construct(
        array|null $storage = null
    )
    {
        if ($storage !== null) {
            $this->storage = $storage;
            return;
        }

        $subscription = new Subscription(
            id: OId::fromString(PrepopulatedTestObjects::SUBSCRIPTION_PENDING_ID),
            customerId: OId::fromString(PrepopulatedTestObjects::CUSTOMER_ID),
            productId: OId::fromString(PrepopulatedTestObjects::PRODUCT_ID),
            price: new Price(1000, Currency::USD),
            status: Status::PENDING,
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('2024-12-31')
        );

        $this->storage = [
            // Example active subscription for testing
            $subscription
        ];
    }

    public function get(OId $id): Subscription
    {
        foreach ($this->storage as $subscription) {
            if ($subscription->getId()->isEqual($id)) {
                return $subscription;
            }
        }

        throw new NotFoundException();
    }

    public function find(OId $id): ?Subscription
    {
        try {
            return $this->get($id);
        } catch (NotFoundException) {
            return null;
        }
    }

    public function add(Subscription $subscription): void
    {
        $this->storage[] = $subscription;
    }

    public function hasActiveSubscription(OId $customerId): bool
    {
        foreach ($this->storage as $subscription) {
            if ($subscription->getCustomerId()->isEqual($customerId) && $subscription->isActive()) {
                return true;
            }
        }

        return false;
    }
}
