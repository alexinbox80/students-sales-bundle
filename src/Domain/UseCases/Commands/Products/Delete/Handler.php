<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Delete;

use alexinbox80\Shared\Domain\EventDispatcherInterface;
use alexinbox80\StudentsSalesBundle\Domain\Repositories\ProductsRepositoryInterface;

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

        $this->productsRepository->remove($product);

        $this->dispatcher->dispatch(...$product->releaseEvents());

        return $product->getId()->toString();
    }
}
