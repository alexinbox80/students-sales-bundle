<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create;

use DateTimeImmutable;
use alexinbox80\Shared\Domain\Model\OId;

final readonly class Command
{
    public function __construct(
        public OId $customerId,
        public OId $productId,
        public DateTimeImmutable $startDate,
    ) {
    }
}
