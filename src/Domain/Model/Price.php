<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * Value Object моделирующий цену
 */
final class Price
{
    private int $amount;

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
