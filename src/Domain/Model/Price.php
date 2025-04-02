<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * Value Object моделирующий цену
 */
#[ORM\Embeddable]
final class Price
{
    #[ORM\Column(name: 'amount', type: 'integer', nullable: false)]
    private int $amount;

    #[ORM\Column(name: 'currency', type: 'string', length: 3, nullable: false)]
    private Currency $currency;

    public function __construct(int $amount, Currency $currency)
    {
        Assert::greaterThan($amount, 0);

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function isEqual(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}
