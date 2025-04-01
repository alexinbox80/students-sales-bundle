<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case RUR = 'RUR';
}
