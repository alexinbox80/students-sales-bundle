<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Queries\Invoices\GeneratePaymentLink;

use DomainException;

final class NoInvoicesAvailableException extends DomainException
{
}
