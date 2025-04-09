<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces;

use DateTime;

interface SoftDeletableInterface
{
    public function getDeletedAt(): ?DateTime;

    public function setDeletedAt(): void;
}
