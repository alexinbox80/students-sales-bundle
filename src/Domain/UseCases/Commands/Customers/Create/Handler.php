<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Create;

use alexinbox80\Shared\Domain\Model\Email;
use alexinbox80\Shared\Domain\Model\Name;
use alexinbox80\StudentsSalesBundle\Domain\Model\Customer;
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
        //$customer = $this->customersRepository->get($command->customerId);

        $customer = Customer::create(
            $command->customerId,
            new Name($command->name->getFirst(), $command->name->getLast()),
            new Email($command->email->toString())
        );

        $this->customersRepository->add($customer);

        $this->dispatcher->dispatch(...$customer->releaseEvents());

        return $customer->getId()->toString();
    }
}
