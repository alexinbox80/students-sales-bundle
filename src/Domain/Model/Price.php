<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model;

use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Status;
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

    #[ORM\Column(name: 'currency', length: 3, nullable: false, enumType: Currency::class)]
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
            && $this->currency->value === $other->currency->value;
    }

    public function __toString(): string
    {
        return $this->amount . ' ' . $this->currency->value;
    }
}
