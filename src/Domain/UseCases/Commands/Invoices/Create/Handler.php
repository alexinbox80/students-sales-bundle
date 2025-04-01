<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Create;

use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Invoice;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\Shared\Domain\FlusherInterface;

final class Handler
{
    public function __construct(
        private readonly SubscriptionsRepositoryInterface $subscriptionsRepository,
        private readonly InvoicesRepositoryInterface $invoices,
        private readonly FlusherInterface $flusher,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Command $command): void
    {
        $subscription = $this->subscriptionsRepository->get($command->subscriptionId);

        $invoice = Invoice::create(
            $subscription->getCustomerId(),
            $subscription->getId(),
            $subscription->getPrice(),
            $command->dueDate,
        );

        $this->invoices->add($invoice);

        $this->flusher->flush();

        $this->dispatcher->dispatch(...$invoice->releaseEvents());
    }
}
