<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Create;

use alexinbox80\StudentsSalesBundle\Domain\Model\Price;

final readonly class Command
{
    public function __construct(
        public string $name,
        public Price $price
    ) {
    }
}
