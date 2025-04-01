<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Invoice;

/**
 * Тоже Value Object в виде enum
 */
enum Status: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case EXPIRED = 'expired';
}
