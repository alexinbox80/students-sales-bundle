<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Queries\Invoices\GeneratePaymentLink;

final readonly class Query
{
    public function __construct(
        public string $subscriptionId
    ) {
    }
}
