<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Update;

use alexinbox80\Shared\Domain\Model\Email;
use alexinbox80\Shared\Domain\Model\Name;
use alexinbox80\Shared\Domain\Model\OId;

final readonly class Command
{
    public function __construct(
        public OId $customerId,
        public Name $name,
        public Email $email
    ) {
    }
}
