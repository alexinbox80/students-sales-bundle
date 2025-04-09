<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces;

use alexinbox80\Shared\Domain\Model\OId;

interface ModelInterface
{
    public function getId(): OId;
}
