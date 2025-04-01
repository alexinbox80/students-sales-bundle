<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\SubmitInvoice;

use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\Shared\Domain\FlusherInterface;

final readonly class Handler
{
    public function __construct(
        private InvoicesRepositoryInterface $invoices,
        private SubscriptionsRepositoryInterface $subscriptionsRepository,
        private FlusherInterface $flusher,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @throws InvoiceIsNotPaidException
     * @throws InvalidSubscriptionStateException
     */
    public function handle(Command $command): void
    {
        $invoice = $this->invoices->get($command->invoiceId);
        if (!$invoice->isPaid()) {
            throw new InvoiceIsNotPaidException();
        }

        $subscription = $this->subscriptionsRepository->get($invoice->getSubscriptionId());

        if ($subscription->isPending()) {
            $subscription->activate();
        } elseif ($subscription->isActive()) {
            $subscription->renew();
        } else {
            throw new InvalidSubscriptionStateException();
        }

        $this->flusher->flush();
        $this->dispatcher->dispatch(...$subscription->releaseEvents());
    }
}
