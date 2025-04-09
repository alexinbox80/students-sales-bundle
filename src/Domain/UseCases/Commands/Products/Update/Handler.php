<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Update;

use alexinbox80\StudentsSalesBundle\Domain\Repositories\ProductsRepositoryInterface;
use alexinbox80\Shared\Domain\EventDispatcherInterface;

final class Handler
{
    public function __construct(
        private readonly ProductsRepositoryInterface $productsRepository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Command $command): string
    {
        $product = $this->productsRepository->get($command->productId);

        $product->update(
            $command->name,
            $command->price
        );

        $this->productsRepository->update();

        $this->dispatcher->dispatch(...$product->releaseEvents());

        return $product->getId()->toString();
    }
}
