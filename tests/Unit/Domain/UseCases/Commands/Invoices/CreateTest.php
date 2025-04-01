<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\UseCases\Commands\Invoices;

use alexinbox80\Shared\Domain\FlusherInterface;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\Shared\Tests\Mocks\EventDispatcherSpy;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Create\Command;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Create\Handler;
use alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories\InMemoryInvoicesRepository;
use alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories\InMemorySubscriptionsRepository;
use alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories\PrepopulatedTestObjects;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Handler::class)]
final class CreateTest extends TestCase
{
    private Handler $handler;
    private InMemorySubscriptionsRepository $subscriptionsRepository;
    private InMemoryInvoicesRepository $invoicesRepository;
    private FlusherInterface $flusher;
    private EventDispatcherSpy $dispatcher;

    protected function setUp(): void
    {
        $this->subscriptionsRepository = new InMemorySubscriptionsRepository();
        $this->invoicesRepository = new InMemoryInvoicesRepository();
        $this->flusher = $this->createMock(FlusherInterface::class);
        $this->dispatcher = new EventDispatcherSpy();

        $this->handler = new Handler(
            $this->subscriptionsRepository,
            $this->invoicesRepository,
            $this->flusher,
            $this->dispatcher
        );
    }

    public function testSuccess(): void
    {
        $initialStorageCount = count($this->invoicesRepository->storage);

        $command = new Command(
            subscriptionId: OId::fromString(PrepopulatedTestObjects::SUBSCRIPTION_PENDING_ID),
            dueDate: new DateTimeImmutable('2024-02-01')
        );

        $this->flusher
            ->expects($this->once())
            ->method('flush');

        $this->handler->handle($command);

        // Verify storage changes
        self::assertCount($initialStorageCount + 1, $this->invoicesRepository->storage);

        // Get the newly created invoice
        $newInvoice = end($this->invoicesRepository->storage);
        $subscription = $this->subscriptionsRepository->get($command->subscriptionId);

        self::assertEquals($subscription->getCustomerId(), $newInvoice->getCustomerId());
        self::assertEquals($subscription->getId(), $newInvoice->getSubscriptionId());
        self::assertEquals($subscription->getPrice()->getAmount(), $newInvoice->getPrice()->getAmount());
        self::assertEquals($subscription->getPrice()->getCurrency(), $newInvoice->getPrice()->getCurrency());
        self::assertEquals($command->dueDate, $newInvoice->getDueDate());

        // Verify events
        $recordedEvents = $this->dispatcher->getRecordedEvents();
        self::assertCount(0, $recordedEvents);
    }
}
