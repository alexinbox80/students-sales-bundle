<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\UseCases\Commands\Subscriptions;

use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionActivatedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Events\SubscriptionRenewedEvent;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Invoice;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\Model\Subscription\Subscription;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\SubscriptionsRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\SubmitInvoice\Command;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\SubmitInvoice\Handler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\SubmitInvoice\InvalidSubscriptionStateException;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\SubmitInvoice\InvoiceIsNotPaidException;
use alexinbox80\Shared\Domain\FlusherInterface;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\Shared\Tests\Mocks\EventDispatcherSpy;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Handler::class)]
final class SubmitInvoiceTest extends TestCase
{
    private Handler $handler;
    private InvoicesRepositoryInterface|MockObject $invoicesRepository;
    private SubscriptionsRepositoryInterface|MockObject $subscriptionsRepository;
    private FlusherInterface|MockObject $flusher;
    private EventDispatcherSpy $dispatcher;

    protected function setUp(): void
    {
        $this->invoicesRepository = $this->createMock(InvoicesRepositoryInterface::class);
        $this->subscriptionsRepository = $this->createMock(SubscriptionsRepositoryInterface::class);
        $this->flusher = $this->createMock(FlusherInterface::class);
        $this->dispatcher = new EventDispatcherSpy();

        $this->handler = new Handler(
            $this->invoicesRepository,
            $this->subscriptionsRepository,
            $this->flusher,
            $this->dispatcher
        );
    }

    public function testActivateSubscriptionWhenPending(): void
    {
        $customerId = OId::next();
        $productId = OId::next();
        $subscription = Subscription::create(
            customerId: $customerId,
            productId: $productId,
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('2024-02-01')
        );
        $subscription->releaseEvents();

        $invoice = Invoice::create(
            customerId: $customerId,
            subscriptionId: $subscription->getId(),
            price: new Price(1000, Currency::USD),
            dueDate: new DateTimeImmutable('2024-01-15')
        );
        $invoice->pay('TX123');
        $invoice->releaseEvents();

        $command = new Command($invoice->getId());

        $this->setupRepositoryMocks($invoice, $subscription);

        $this->flusher
            ->expects($this->once())
            ->method('flush');

        $this->handler->handle($command);

        self::assertTrue($subscription->isActive());
        $recordedEvents = $this->dispatcher->getRecordedEvents();
        self::assertCount(1, $recordedEvents);
        self::assertInstanceOf(SubscriptionActivatedEvent::class, $recordedEvents[0]);
    }

    public function testRenewSubscriptionWhenActive(): void
    {
        $customerId = OId::next();
        $productId = OId::next();
        $subscription = Subscription::create(
            customerId: $customerId,
            productId: $productId,
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('now +3 days')
        );
        $subscription->activate();
        $subscription->releaseEvents();

        $invoice = Invoice::create(
            customerId: $customerId,
            subscriptionId: $subscription->getId(),
            price: new Price(1000, Currency::USD),
            dueDate: new DateTimeImmutable('2024-01-15')
        );
        $invoice->pay('TX123');
        $invoice->releaseEvents();

        $command = new Command($invoice->getId());

        $this->setupRepositoryMocks($invoice, $subscription);

        $this->flusher
            ->expects($this->once())
            ->method('flush');

        $this->handler->handle($command);

        self::assertTrue($subscription->isActive());
        $recordedEvents = $this->dispatcher->getRecordedEvents();
        self::assertCount(1, $recordedEvents);
        self::assertInstanceOf(SubscriptionRenewedEvent::class, $recordedEvents[0]);
    }

    public function testFailsWhenInvoiceNotPaid(): void
    {
        $customerId = OId::next();
        $productId = OId::next();
        $subscription = Subscription::create(
            customerId: $customerId,
            productId: $productId,
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('2024-02-01')
        );
        $subscription->releaseEvents();

        $invoice = Invoice::create(
            customerId: $customerId,
            subscriptionId: $subscription->getId(),
            price: new Price(1000, Currency::USD),
            dueDate: new DateTimeImmutable('2024-01-15')
        );
        $invoice->releaseEvents();

        $command = new Command($invoice->getId());

        $this->invoicesRepository
            ->expects($this->once())
            ->method('get')
            ->with($invoice->getId())
            ->willReturn($invoice);

        $this->flusher
            ->expects($this->never())
            ->method('flush');

        $this->expectException(InvoiceIsNotPaidException::class);
        $this->handler->handle($command);
    }

    public function testFailsWhenSubscriptionInInvalidState(): void
    {
        $customerId = OId::next();
        $productId = OId::next();
        $subscription = Subscription::create(
            customerId: $customerId,
            productId: $productId,
            price: new Price(1000, Currency::USD),
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('2024-02-01')
        );
        $subscription->activate();
        $subscription->cancel();
        $subscription->releaseEvents();

        $invoice = Invoice::create(
            customerId: $customerId,
            subscriptionId: $subscription->getId(),
            price: new Price(1000, Currency::USD),
            dueDate: new DateTimeImmutable('2024-01-15')
        );
        $invoice->pay('TX123');
        $invoice->releaseEvents();

        $command = new Command($invoice->getId());

        $this->setupRepositoryMocks($invoice, $subscription);
        $this->flusher
            ->expects($this->never())
            ->method('flush');

        $this->expectException(InvalidSubscriptionStateException::class);
        $this->handler->handle($command);
    }

    private function setupRepositoryMocks(Invoice $invoice, Subscription $subscription): void
    {
        $this->invoicesRepository
            ->expects($this->once())
            ->method('get')
            ->with($invoice->getId())
            ->willReturn($invoice);

        $this->subscriptionsRepository
            ->expects($this->once())
            ->method('get')
            ->with($subscription->getId())
            ->willReturn($subscription);
    }
}
