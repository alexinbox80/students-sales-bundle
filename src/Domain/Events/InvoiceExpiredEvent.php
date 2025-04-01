<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Events;

use alexinbox80\Shared\Domain\Events\AbstractDomainEvent;
use alexinbox80\Shared\Domain\Model\OId;

class InvoiceExpiredEvent extends AbstractDomainEvent
{
    public function __construct(
        OId $invoiceId,
    ) {
        parent::__construct($invoiceId);
    }
}
