<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Invoice;

use alexinbox80\StudentsSalesBundle\Domain\Events\InvoiceExpiredEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\InvoicePaidEvent;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\Shared\Domain\Events\EventsTrait;
use alexinbox80\Shared\Domain\Model\AggregateRootInterface;
use alexinbox80\Shared\Domain\Model\OId;
use DateTimeImmutable;

/**
 * Агрегат "Счет"
 * Этот агрегат состоит более чем с одной сущностью.
 * LineItems - его составная часть.
 * Мы не используем в примере LineItems, но здесь я добавил метод addLineItem для демонстрации.
 */
class Invoice implements AggregateRootInterface
{
    use EventsTrait;

    /**
     * @param LineItem[] $items
     */
    public function __construct(
        private OId $id,
        private Status $status,
        private OId $customerId,
        private OId $subscriptionId,
        private Price $price,
        private DateTimeImmutable $dueDate,
        private ?DateTimeImmutable $createdAt = null,
        private ?DateTimeImmutable $paidAt = null,
        private ?DateTimeImmutable $expiredAt = null,
        private ?string $transactionId = null,
        private ?array $items = [],

    ) {
        $this->createdAt = $this->createdAt ?? new DateTimeImmutable();
    }

    public static function create(
        OId $customerId,
        OId $subscriptionId,
        Price $price,
        DateTimeImmutable $dueDate
    ): self {
        return new Invoice(
            OId::next(),
            Status::PENDING,
            $customerId,
            $subscriptionId,
            $price,
            $dueDate
        );
    }

    public function pay(string $transactionId): void
    {
        if ($this->status !== Status::PENDING) {
            throw new InvoiceIsNotAwaitingPaymentException();
        }

        $this->status = Status::PAID;
        $this->transactionId = $transactionId;
        $this->paidAt = new DateTimeImmutable();

        $this->recordEvent(new InvoicePaidEvent($this->id));
    }

    public function expire(): void
    {
        if ($this->status !== Status::PENDING) {
            throw new InvoiceIsNotAwaitingPaymentException();
        }

        $this->status = Status::EXPIRED;
        $this->expiredAt = new DateTimeImmutable();
        $this->recordEvent(new InvoiceExpiredEvent($this->id));
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getCustomerId(): OId
    {
        return $this->customerId;
    }

    public function getSubscriptionId(): OId
    {
        return $this->subscriptionId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDueDate(): DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getExpiredAt(): ?DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function isPaid(): bool
    {
        return $this->status === Status::PAID;
    }

    public function isExpired(): bool
    {
        return $this->status === Status::EXPIRED;
    }

    public function addLineItem(string $productId, int $amount, int $quantity, string $text): void
    {
        $this->items[] = new LineItem($productId, $amount, $quantity, $text);
    }
}
