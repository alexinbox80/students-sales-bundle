<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model\Subscription;

use alexinbox80\Shared\Domain\Events\EventsTrait;
use alexinbox80\Shared\Domain\Model\AggregateRootInterface;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionActivatedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionCancelledEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionCreatedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionExpiredEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionRenewedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\SubscriptionIsNotActiveException;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\SubscriptionIsNotPendingException;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\SubscriptionIsNotReadyForRenewalException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces\ModelInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces\SoftDeletableInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Interfaces\HasMetaTimestampsInterface;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\CreatedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\DeletedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\UpdatedAtTrait;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DomainException;

/**
 * Агрегат "Подписка".
 * Подписка пользователя на продукт.
 * Обратите внимание, что все ссылки на другие агрегаты - это их идентификаторы, а не объекты.
 * У этого агрегата есть методы мутаторы с бизнес логикой.
 */
#[ORM\Table(name: 'subscriptions')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Subscription implements AggregateRootInterface, ModelInterface//, HasMetaTimestampsInterface, SoftDeletableInterface
{
    use CreatedAtTrait, UpdatedAtTrait;//, DeletedAtTrait;
    use EventsTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'shared__oid', unique: true)]
    private OId $id;

    #[ORM\Column(type: 'shared__oid')]
    private OId $customerId;

    #[ORM\Column(type: 'shared__oid')]
    private OId $productId;

    #[ORM\Embedded(class: Price::class, columnPrefix: false)]
    private Price $price;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    private Status $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    public function __construct(
        OId $id,
        OId $customerId,
        OId $productId,
        Price $price,
        Status $status,
        DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate = null
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->productId = $productId;
        $this->price = $price;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public static function create(
        OId $customerId,
        OId $productId,
        Price $price,
        DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate = null
    ): self {
        $subscription =  new self(
            OId::next(),
            $customerId,
            $productId,
            $price,
            Status::PENDING,
            $startDate,
            $endDate,
        );

        $subscription->recordEvent(
            new SubscriptionCreatedEvent(
                $subscription->getId(),
            )
        );

        return $subscription;
    }

    public function activate(): void
    {
        if ($this->status !== Status::PENDING) {
            throw new SubscriptionIsNotPendingException();
        }
        $this->status = Status::ACTIVE;

        $this->recordEvent(
            new SubscriptionActivatedEvent(
                $this->getId(),
            )
        );
    }

    public function cancel(): void
    {
        if ($this->status !== Status::ACTIVE) {
            throw new DomainException('Only active subscriptions can be cancelled');
        }
        $this->status = Status::CANCELLED;
        $this->endDate = new DateTimeImmutable();

        $this->recordEvent(
            new SubscriptionCancelledEvent(
                $this->getId(),
            )
        );
    }

    public function expire(): void
    {
        if ($this->status !== Status::ACTIVE) {
            throw new DomainException('Only active subscriptions can expire');
        }
        $this->status = Status::EXPIRED;
        $this->endDate = new DateTimeImmutable();

        $this->recordEvent(
            new SubscriptionExpiredEvent(
                $this->getId(),
            )
        );
    }

    public function renew(): void
    {
        if ($this->status !== Status::ACTIVE) {
            throw new SubscriptionIsNotActiveException();
        }

        if ($this->endDate === null) {
            throw new DomainException('Cannot renew subscription without end date');
        }

        $oneWeekFromNow = new DateTimeImmutable('+1 week');
        if ($this->endDate > $oneWeekFromNow) {
            throw new SubscriptionIsNotReadyForRenewalException();
        }

        $this->endDate = $this->endDate->modify('+1 month');

        $this->recordEvent(
            new SubscriptionRenewedEvent(
                $this->getId(),
            )
        );
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getCustomerId(): OId
    {
        return $this->customerId;
    }

    public function getProductId(): OId
    {
        return $this->productId;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isPending(): bool
    {
        return $this->status === Status::PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->status === Status::EXPIRED;
    }
}
