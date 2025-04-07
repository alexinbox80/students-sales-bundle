<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Delete;

use alexinbox80\StudentsSalesBundle\Domain\Repositories\CustomersRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;

final class Handler
{
    public function __construct(
        private readonly CustomersRepositoryInterface $customersRepository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Command $command): string
    {
        $customer = $this->customersRepository->get($command->customerId);

        $this->customersRepository->remove($customer);

        $this->dispatcher->dispatch(...$customer->releaseEvents());

        return $customer->getId()->toString();
    }
}
