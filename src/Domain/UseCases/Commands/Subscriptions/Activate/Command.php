<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Activate;

use alexinbox80\Shared\Domain\Model\OId;

final readonly class Command
{
    public function __construct(
        public OId $subscriptionId,
    ) {
    }
}
