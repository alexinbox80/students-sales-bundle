<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\UseCases\Commands\Invoices;

use alexinbox80\StudentsSalesBundle\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use alexinbox80\StudentsSalesBundle\Domain\Model\Invoice\Invoice;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\InvoicesRepositoryInterface;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Expire\Command;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Invoices\Expire\Handler;
use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\Shared\Domain\FlusherInterface;
use alexinbox80\Shared\Domain\Model\OId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Handler::class)]
final class ExpireTest extends TestCase
{
    private Handler $handler;
    private InvoicesRepositoryInterface|MockObject $invoicesRepository;
    private FlusherInterface|MockObject $flusher;
    private EventDispatcherInterface|MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->invoicesRepository = $this->createMock(InvoicesRepositoryInterface::class);
        $this->flusher = $this->createMock(FlusherInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->handler = new Handler(
            $this->invoicesRepository,
            $this->flusher,
            $this->dispatcher
        );
    }

    public function testSuccess(): void
    {
        $invoiceId = OId::next();
        $command = new Command(invoiceId: $invoiceId);

        $invoice = $this->createMock(Invoice::class);
        $invoice
            ->expects($this->once())
            ->method('expire');

        $invoice
            ->expects($this->once())
            ->method('releaseEvents')
            ->willReturn([]);

        $this->invoicesRepository
            ->expects($this->once())
            ->method('get')
            ->with($command->invoiceId)
            ->willReturn($invoice);

        $this->flusher
            ->expects($this->once())
            ->method('flush');

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch');

        $this->handler->handle($command);
    }

    public function testFailsWhenInvoiceNotAwaitingPayment(): void
    {
        $invoiceId = OId::next();
        $command = new Command(invoiceId: $invoiceId);

        $invoice = $this->createMock(Invoice::class);
        $invoice
            ->expects($this->once())
            ->method('expire')
            ->willThrowException(new InvoiceIsNotAwaitingPaymentException());

        $this->invoicesRepository
            ->expects($this->once())
            ->method('get')
            ->with($command->invoiceId)
            ->willReturn($invoice);

        $this->expectException(InvoiceIsNotAwaitingPaymentException::class);
        $this->handler->handle($command);
    }
}
