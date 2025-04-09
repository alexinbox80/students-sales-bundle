<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Invoice;

use alexinbox80\Shared\Domain\Events\EventsTrait;
use alexinbox80\Shared\Domain\Model\AggregateRootInterface;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Events\InvoiceExpiredEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\InvoicePaidEvent;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces\HasMetaTimestampsInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces\ModelInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces\SoftDeletableInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\DeletedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\UpdatedAtTrait;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Агрегат "Счет"
 * Этот агрегат состоит более чем с одной сущностью.
 * LineItems - его составная часть.
 * Мы не используем в примере LineItems, но здесь я добавил метод addLineItem для демонстрации.
 */
#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
#[ORM\HasLifecycleCallbacks]
class Invoice implements AggregateRootInterface, ModelInterface, HasMetaTimestampsInterface, SoftDeletableInterface
{
    use UpdatedAtTrait, DeletedAtTrait;
    use EventsTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'shared__oid', unique: true)]
    private OId $id;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    private Status $status;

    #[ORM\Column(type: 'shared__oid')]
    private OId $customerId;

    #[ORM\Column(type: 'shared__oid')]
    private OId $subscriptionId;

    #[ORM\Embedded(class: Price::class, columnPrefix: false)]
    private Price $price;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $dueDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $expiredAt = null;

    #[ORM\Column(type: 'string', length:255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(type: 'json', length: 1024)]
    private ?array $items = [];

    /**
     * @param LineItem[] $items
     */
    public function __construct(
        OId $id,
        Status $status,
        OId $customerId,
        OId $subscriptionId,
        Price $price,
        DateTimeImmutable $dueDate,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $paidAt = null,
        ?DateTimeImmutable $expiredAt = null,
        ?string $transactionId = null,
        ?array $items = [],

    ) {
        $this->id = $id;
        $this->status = $status;
        $this->customerId = $customerId;
        $this->subscriptionId = $subscriptionId;
        $this->price = $price;
        $this->dueDate = $dueDate;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->paidAt = $paidAt ?? new DateTimeImmutable();
        $this->expiredAt = $expiredAt ?? new DateTimeImmutable();
        $this->transactionId = $transactionId;
        $this->items = $items;
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

    public function setCreatedAt(): void
    {
        $this->createdAt = new DateTimeImmutable();
    }
}
