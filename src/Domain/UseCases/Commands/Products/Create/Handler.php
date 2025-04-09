<?php

namespace alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Create;

use alexinbox80\StudentsSalesBundle\Domain\Model\Product;
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
        $product = Product::create(
            $command->name,
            $command->price
        );

        $this->productsRepository->add($product);

        $this->dispatcher->dispatch(...$product->releaseEvents());

        return $product->getId()->toString();
    }
}
