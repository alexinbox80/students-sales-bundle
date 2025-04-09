<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Invoice;
use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Status;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\Shared\Domain\Model\OId;
use DateTimeImmutable;

final class InMemoryInvoicesRepository implements InvoicesRepositoryInterface
{
    /** @var Invoice[] */
    public array $storage;

    /**
     * @param Invoice[]|null $storage
     */
    public function __construct(
        array|null $storage = null
    ) {
        if ($storage !== null) {
            $this->storage = $storage;
            return;
        }

        $invoice = new Invoice(
            id: OId::fromString(PrepopulatedTestObjects::INVOICE_PENDING_ID),
            status: Status::PENDING,
            customerId: OId::fromString(PrepopulatedTestObjects::CUSTOMER_ID),
            subscriptionId: OId::fromString(PrepopulatedTestObjects::SUBSCRIPTION_PENDING_ID),
            price: new Price(1000, Currency::USD),
            dueDate: new DateTimeImmutable('2024-01-15')
        );

        $this->storage = [
            // Example pending invoice for testing
            $invoice
        ];
    }

    public function get(OId $id): Invoice
    {
        foreach ($this->storage as $invoice) {
            if ($invoice->getId()->isEqual($id)) {
                return $invoice;
            }
        }

        throw new NotFoundException();
    }

    public function find(OId $id): ?Invoice
    {
        try {
            return $this->get($id);
        } catch (NotFoundException) {
            return null;
        }
    }

    public function add(Invoice $invoice): void
    {
        $this->storage[] = $invoice;
    }

    public function update(): void
    {

    }

    public function findLatestPendingInvoiceForSubscription(OId $subscriptionId): ?Invoice
    {
        $latestInvoice = null;
        $latestDate = null;

        foreach ($this->storage as $invoice) {
            if (!$invoice->getSubscriptionId()->isEqual($subscriptionId)) {
                continue;
            }

            if ($invoice->isPaid() || $invoice->isExpired()) {
                continue;
            }

            if ($latestDate === null || $invoice->getCreatedAt() > $latestDate) {
                $latestInvoice = $invoice;
                $latestDate = $invoice->getCreatedAt();
            }
        }

        return $latestInvoice;
    }
}
