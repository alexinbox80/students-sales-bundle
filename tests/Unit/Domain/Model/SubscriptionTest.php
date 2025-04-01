<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\Model;

use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionActivatedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionCancelledEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionCreatedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionExpiredEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionRenewedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\SubscriptionIsNotActiveException;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\SubscriptionIsNotReadyForRenewalException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\Shared\Domain\Model\OId;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Subscription::class)]
final class SubscriptionTest extends TestCase
{
    public function testRenewSuccessfully(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('now +3 days')
        );

        $subscription->activate();
        $subscription->renew();

        self::assertTrue($subscription->isActive());

        $events = $subscription->releaseEvents();
        self::assertCount(3, $events);
        self::assertInstanceOf(SubscriptionRenewedEvent::class, $events[2]);
    }

    public function testCannotRenewInactiveSubscription(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('now +3 days')
        );

        $this->expectException(SubscriptionIsNotActiveException::class);
        $subscription->renew();
    }

    public function testCannotRenewSubscriptionWithoutEndDate(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01')
        );

        $subscription->activate();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot renew subscription without end date');
        $subscription->renew();
    }

    public function testCannotRenewSubscriptionTooEarly(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('now +2 weeks')
        );

        $subscription->activate();

        $this->expectException(SubscriptionIsNotReadyForRenewalException::class);
        $subscription->renew();
    }

    public function testActivateSuccessfully(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01')
        );

        $subscription->activate();

        self::assertTrue($subscription->isActive());

        $events = $subscription->releaseEvents();

        self::assertCount(2, $events);
        self::assertInstanceOf(SubscriptionActivatedEvent::class, $events[1]);
    }

    public function testCancelSuccessfully(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01')
        );

        $subscription->activate();
        $subscription->cancel();

        self::assertTrue($subscription->isCancelled());

        $events = $subscription->releaseEvents();

        self::assertCount(3, $events);
        self::assertInstanceOf(SubscriptionCancelledEvent::class, $events[2]);
    }

    public function testExpireSuccessfully(): void
    {
        $subscription = Subscription::create(
            customerId: OId::next(),
            productId: OId::next(),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01')
        );

        $subscription->activate();
        $subscription->expire();

        self::assertTrue($subscription->isExpired());

        $events = $subscription->releaseEvents();

        self::assertCount(3, $events);
        self::assertInstanceOf(SubscriptionExpiredEvent::class, $events[2]);
    }
}
