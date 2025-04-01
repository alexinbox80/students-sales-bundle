<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\UseCases\Commands\Subscriptions;

use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionCreatedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create\Command;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create\CustomerAlreadyHasActiveSubscriptionException;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create\Handler;
use alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories\InMemoryProductsRepository;
use alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories\InMemorySubscriptionsRepository;
use alexinbox80\Shared\Domain\FlusherInterface;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\Shared\Tests\Mocks\EventDispatcherSpy;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Handler::class)]
final class CreateTest extends TestCase
{
    private Handler $handler;
    private InMemorySubscriptionsRepository $subscriptionsRepository;
    private InMemoryProductsRepository $productsRepository;
    private FlusherInterface $flusher;
    private EventDispatcherSpy $dispatcher;

    protected function setUp(): void
    {
        $this->subscriptionsRepository = new InMemorySubscriptionsRepository([]);
        $this->productsRepository = new InMemoryProductsRepository();
        $this->flusher = $this->createMock(FlusherInterface::class);
        $this->dispatcher = new EventDispatcherSpy();

        $this->handler = new Handler(
            $this->subscriptionsRepository,
            $this->productsRepository,
            $this->flusher,
            $this->dispatcher
        );
    }

    public function testSuccess(): void
    {
        $customerId = OId::next();
        $productId = $this->productsRepository->storage[0]->getId();
        $startDate = new DateTimeImmutable('2024-01-01');

        $command = new Command(
            customerId: $customerId,
            productId: $productId,
            startDate: $startDate,
        );

        $this->flusher
            ->expects($this->once())
            ->method('flush');

        $this->handler->handle($command);

        self::assertCount(1, $this->subscriptionsRepository->storage);

        $subscription = $this->subscriptionsRepository->storage[0];
        self::assertEquals($customerId, $subscription->getCustomerId());
        self::assertEquals($productId, $subscription->getProductId());
        self::assertEquals(1000, $subscription->getPrice()->getAmount());
        self::assertEquals(Currency::USD, $subscription->getPrice()->getCurrency());
        self::assertEquals($startDate, $subscription->getStartDate());
        self::assertEquals($startDate->modify('+1 month'), $subscription->getEndDate());

        $recordedEvents = $this->dispatcher->getRecordedEvents();
        self::assertCount(1, $recordedEvents);
        self::assertInstanceOf(SubscriptionCreatedEvent::class, $recordedEvents[0]);
    }

    public function testFailsWhenCustomerHasActiveSubscription(): void
    {
        $customerId = OId::next();

        $existingSubscription = Subscription::create(
            customerId: $customerId,
            productId: OId::fromString('8bc55b76-3c6b-4644-9251-9c093b2c5a17'),
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('2024-12-31')
        );
        $existingSubscription->activate();
        $existingSubscription->releaseEvents();

        $this->subscriptionsRepository->storage[] = $existingSubscription;

        $command = new Command(
            customerId: $customerId,
            productId: OId::next(),
            startDate: new DateTimeImmutable('2024-01-01'),
        );

        $this->expectException(CustomerAlreadyHasActiveSubscriptionException::class);
        $this->handler->handle($command);
    }
}
