<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Subscription;

/**
 * Тоже Value Object в виде enum
 */
enum Status: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}
