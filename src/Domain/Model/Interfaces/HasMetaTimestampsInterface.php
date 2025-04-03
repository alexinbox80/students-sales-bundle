<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces;

interface HasMetaTimestampsInterface
{
    public function setCreatedAt(): void;

    public function setUpdatedAt(): void;
}
