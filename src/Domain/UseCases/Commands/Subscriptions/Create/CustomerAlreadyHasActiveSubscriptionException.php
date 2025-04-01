<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create;

use DomainException;

final class CustomerAlreadyHasActiveSubscriptionException extends DomainException
{
}
