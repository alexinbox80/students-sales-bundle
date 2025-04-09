<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait DeletedAtTrait
{
    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    private ?DateTime $deletedAt = null;

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(): void
    {
        if ($this->deletedAt === null) {
            $this->deletedAt = new DateTime();
        }
    }
}
