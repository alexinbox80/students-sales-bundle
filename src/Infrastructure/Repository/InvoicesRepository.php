<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure\Repository;

use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Invoice;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<Invoice>
 */
class InvoicesRepository extends AbstractRepository implements InvoicesRepositoryInterface
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($entityManager);
    }

    public function get(OId $id): Invoice
    {
        $repository = $this->entityManager->getRepository(Invoice::class);
        /** @var Invoice|null $invoice */
        $invoice = $repository->find($id);

        return $invoice;
    }

    public function find(OId $id): ?Invoice
    {
        return $this->find($id);
    }

    public function add(Invoice $invoice): void
    {
        $this->store($invoice);
    }

    public function update(): void
    {
        $this->flush();
    }

    public function findLatestPendingInvoiceForSubscription(OId $subscriptionId): ?Invoice
    {
        $invoices = $this->entityManager->getRepository(Invoice::class)->findBy(['subscriptionId' => $subscriptionId]);

        $latestInvoice = null;
        $latestDate = null;

        foreach ($invoices as $invoice) {
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
