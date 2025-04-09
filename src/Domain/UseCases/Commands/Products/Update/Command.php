<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Update;

use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;

final readonly class Command
{
    public function __construct(
        public OId $productId,
        public string $name,
        public Price $price
    ) {
    }
}
