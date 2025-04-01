<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Repositories;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Invoice;
use alexinbox80\Shared\Domain\Model\OId;

interface InvoicesRepositoryInterface
{
    /**
     * @throws NotFoundException
     */
    public function get(OId $id): Invoice;

    public function find(OId $id): ?Invoice;

    public function add(Invoice $invoice): void;

    public function findLatestPendingInvoiceForSubscription(OId $subscriptionId): ?Invoice;
}
