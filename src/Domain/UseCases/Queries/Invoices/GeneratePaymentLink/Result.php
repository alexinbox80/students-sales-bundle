<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Queries\Invoices\GeneratePaymentLink;

final readonly class Result
{
    public function __construct(
        public string $paymentLink
    ) {
    }
}
