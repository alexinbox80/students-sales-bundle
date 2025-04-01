<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Activate;

use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\Shared\Domain\FlusherInterface;

final readonly class Handler
{
    public function __construct(
        private SubscriptionsRepositoryInterface $subscriptions,
        private FlusherInterface $flusher,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    public function handle(Command $command): void
    {
        $subscription = $this->subscriptions->get($command->subscriptionId);

        $subscription->activate();

        $this->flusher->flush();

        $this->dispatcher->dispatch(...$subscription->releaseEvents());
    }
}
