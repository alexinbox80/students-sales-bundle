<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Expire;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\Shared\Domain\FlusherInterface;

final class Handler
{
    public function __construct(
        private readonly InvoicesRepositoryInterface $invoicesRepository,
    //    private readonly FlusherInterface $flusher,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws InvoiceIsNotAwaitingPaymentException
     */
    public function handle(Command $command): void
    {
        $invoice = $this->invoicesRepository->get($command->invoiceId);

        $invoice->expire();

        $this->invoicesRepository->update();

        //$this->flusher->flush();

        $this->dispatcher->dispatch(...$invoice->releaseEvents());
    }
}
