<?php

namespace alexinbox80\StudentsSalesBundle\Domain;

interface PaymentGatewayInterface
{
    public function getPaymentLink(string $invoiceId): string;
}
