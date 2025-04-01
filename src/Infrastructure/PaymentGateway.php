<?php

namespace alexinbox80\StudentsSalesBundle\Infrastructure;

use alexinbox80\StudentsSalesBundle\Domain\PaymentGatewayInterface;

class PaymentGateway implements PaymentGatewayInterface
{
    public function getPaymentLink(string $invoiceId): string
    {
        return "https://payment-gateway.com/pay?invoiceId=$invoiceId";
    }
}
