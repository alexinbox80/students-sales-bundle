<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTrait
{
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private DateTime $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        //if ($this->createdAt === null) {
            $this->createdAt = new DateTime();
        //}
    }
}
