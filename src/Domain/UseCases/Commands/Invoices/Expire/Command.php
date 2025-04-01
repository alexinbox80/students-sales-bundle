<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Expire;

use alexinbox80\Shared\Domain\Model\OId;

final readonly class Command
{
    public function __construct(
        public OId $invoiceId,
    ) {
    }
}
