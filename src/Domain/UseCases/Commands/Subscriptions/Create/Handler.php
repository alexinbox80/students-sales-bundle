<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create;

use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\ProductsRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\Shared\Domain\FlusherInterface;

final class Handler
{
    public function __construct(
        private readonly SubscriptionsRepositoryInterface $subscriptions,
        private readonly ProductsRepositoryInterface $productsRepository,
        private readonly FlusherInterface $flusher,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @throws CustomerAlreadyHasActiveSubscriptionException
     */
    public function handle(Command $command): string
    {
        if ($this->subscriptions->hasActiveSubscription($command->customerId)) {
            throw new CustomerAlreadyHasActiveSubscriptionException();
        }

        $product = $this->productsRepository->get($command->productId);
        $endDate = $command->startDate->modify('+1 month');

        $subscription = Subscription::create(
            $command->customerId,
            $command->productId,
            $product->getPrice(),
            $command->startDate,
            $endDate
        );

        $this->subscriptions->add($subscription);
        //$this->flusher->flush();

        $this->dispatcher->dispatch(...$subscription->releaseEvents());

        return $subscription->getId()->toString();
    }
}
