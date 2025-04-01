<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Create;

use alexinbox80\Shared\Domain\Model\OId;
use DateTimeImmutable;

final readonly class Command
{
    public function __construct(
        public OId $subscriptionId,
        public DateTimeImmutable $dueDate,
    ) {
    }
}
