<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Queries\Invoices\GeneratePaymentLink;

use alexinbox80\StudentsSalesBundle\Domain\PaymentGatewayInterface;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Infrastructure\PaymentGateway;
use alexinbox80\Shared\Domain\Model\OId;

final readonly class Fetcher
{
    public function __construct(
        public InvoicesRepositoryInterface $invoicesRepository,
        // TODO change PaymentGateway to PaymentGatewayInterface
        public PaymentGatewayInterface $paymentGateway,
    ) {
    }

    /**
     * @throws NoInvoicesAvailableException
     */
    public function fetch(Query $query): Result
    {
        $subscriptionId = OId::fromString($query->subscriptionId);
        $invoice = $this->invoicesRepository->findLatestPendingInvoiceForSubscription($subscriptionId);

        if (!$invoice) {
            throw new NoInvoicesAvailableException();
        }

        return new Result(
            $this->paymentGateway->getPaymentLink($invoice->getId()->toString())
        );
    }
}
