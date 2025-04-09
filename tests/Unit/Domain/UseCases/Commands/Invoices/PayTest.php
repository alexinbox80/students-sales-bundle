<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\UseCases\Commands\Invoices;

use alexinbox80\StudentsSalesBundle\Domain\Events\InvoicePaidEvent;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use alexinbox80\StudentsSalesBundle\Domain\Exceptions\NotFoundException;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Pay\Command;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Pay\Handler;
use alexinbox80\StudentsSalesBundle\Tests\Mocks\Repositories\InMemoryInvoicesRepository;
use alexinbox80\Shared\Domain\FlusherInterface;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\Shared\Tests\Mocks\EventDispatcherSpy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Handler::class)]
final class PayTest extends TestCase
{
    private Handler $handler;
    private InMemoryInvoicesRepository $invoicesRepository;
    private FlusherInterface $flusher;
    private EventDispatcherSpy $dispatcher;

    protected function setUp(): void
    {
        $this->invoicesRepository = new InMemoryInvoicesRepository();
//        $this->flusher = $this->createMock(FlusherInterface::class);
        $this->dispatcher = new EventDispatcherSpy();

        $this->handler = new Handler(
            $this->invoicesRepository,
            //$this->flusher,
            $this->dispatcher
        );
    }

    public function testSuccess(): void
    {
        // Use the preexisting invoice from InMemoryInvoicesRepository
        $invoice = $this->invoicesRepository->storage[0];
        $command = new Command(
            invoiceId: $invoice->getId(),
            transactionId: 'transaction-123'
        );

//        $this->flusher
//            ->expects($this->once())
//            ->method('flush');

        $this->handler->handle($command);

        // Verify invoice state changes
        $updatedInvoice = $this->invoicesRepository->get($invoice->getId());
        self::assertTrue($updatedInvoice->isPaid());
        self::assertEquals('transaction-123', $updatedInvoice->getTransactionId());
        self::assertNotNull($updatedInvoice->getPaidAt());

        // Verify events
        $recordedEvents = $this->dispatcher->getRecordedEvents();
        self::assertCount(1, $recordedEvents);
        self::assertInstanceOf(InvoicePaidEvent::class, $recordedEvents[0]);
    }

    public function testFailsWhenInvoiceNotFound(): void
    {
        $command = new Command(
            invoiceId: OId::next(),
            transactionId: 'transaction-123'
        );

        $this->expectException(NotFoundException::class);
        $this->handler->handle($command);
    }

    public function testFailsWhenInvoiceAlreadyPaid(): void
    {
        // Get and pay the preexisting invoice first
        $invoice = $this->invoicesRepository->storage[0];
        $invoice->pay('first-transaction');

        $command = new Command(
            invoiceId: $invoice->getId(),
            transactionId: 'second-transaction'
        );

        $this->expectException(InvoiceIsNotAwaitingPaymentException::class);
        $this->handler->handle($command);
    }
}
