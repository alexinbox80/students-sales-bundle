<?php

namespace alexinbox80\StudentsSalesBundle\Presentation\Contract;

use alexinbox80\Shared\Domain\Model\Email;
use alexinbox80\Shared\Domain\Model\Name;
use alexinbox80\StudentsSalesBundle\Domain\Model\Currency;
use alexinbox80\StudentsSalesBundle\Domain\Model\Price;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create\Command as CreateSubscriptionCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Subscriptions\Create\Handler as CreateSubscriptionHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Create\Command as CreateCustomerCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Create\Handler as CreateCustomerHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Update\Command as UpdateCustomerCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Update\Handler as UpdateCustomerHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Delete\Command as DeleteCustomerCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Customers\Delete\Handler as DeleteCustomerHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Create\Command as CreateProductCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Create\Handler as CreateProductHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Update\Command as UpdateProductCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Update\Handler as UpdateProductHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Delete\Command as DeleteProductCommand;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Commands\Products\Delete\Handler as DeleteProductHandler;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Queries\Invoices\GeneratePaymentLink\Fetcher as GeneratePaymentLinkFetcher;
use alexinbox80\StudentsSalesBundle\Domain\UseCases\Queries\Invoices\GeneratePaymentLink\Query as GeneratePaymentLinkQuery;
use DateTimeImmutable;
use alexinbox80\Shared\Domain\Model\OId;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Throwable;

/**
 * Реализация контракта с прямым доступом к коду.
 *
 * Использует механизм Symfony Service Subscriber, чтобы не инстанциировать все сервисы одновременно.
 * https://symfony.com/doc/current/service_container/service_subscribers_locators.html
 */
final readonly class Sales implements SalesInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $locator
    ) {
    }

    public function subscribe(
        string $customerId,
        string $productId,
        DateTimeImmutable $startDate,
    ): string {
        $command = new CreateSubscriptionCommand(
            customerId: OId::fromString($customerId),
            productId: OId::fromString($productId),
            startDate: $startDate
        );

        $handler = $this->service(CreateSubscriptionHandler::class);

        try {
            $subscriptionId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $subscriptionId;
    }

    public function generatePaymentLink(string $subscriptionId): string
    {
        $fetcher = $this->service(GeneratePaymentLinkFetcher::class);

        try {
            $result = $fetcher->fetch(new GeneratePaymentLinkQuery(
                subscriptionId: $subscriptionId
            ));

            return $result->paymentLink;
        } catch (Exception $e) {
            // TODO Convert exception to contract
            throw $e;
        }
    }

    public function createCustomer(
        string $customerId,
        string $firstName,
        string $lastName,
        string $email,
    ): string
    {
        $command = new CreateCustomerCommand(
            customerId: OId::fromString($customerId),
            name: new Name($firstName, $lastName),
            email: new Email($email)
        );

        $handler = $this->service(CreateCustomerHandler::class);

        try {
            $customerId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $customerId;
    }

    public function updateCustomer(
        string $customerId,
        string $firstName,
        string $lastName,
        string $email,
    ): string
    {
        $command = new UpdateCustomerCommand(
            customerId: OId::fromString($customerId),
            name: new Name($firstName, $lastName),
            email: new Email($email)
        );

        $handler = $this->service(UpdateCustomerHandler::class);

        try {
            $customerId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $customerId;
    }

    public function deleteCustomer(
        string $customerId,
    ): string
    {
        $command = new DeleteCustomerCommand(
            customerId: OId::fromString($customerId)
        );

        $handler = $this->service(DeleteCustomerHandler::class);

        try {
            $customerId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $customerId;
    }

    public function createProduct(
        string $name,
        string $amount,
        string $currency,
    ): string
    {
        if ($currency === 'usd') $currency = Currency::USD;
            elseif ($currency === 'eur') $currency = Currency::EUR;
                else $currency = Currency::RUR;

        $command = new CreateProductCommand(
            name: $name,
            price: new Price($amount, $currency)
        );

        $handler = $this->service(CreateProductHandler::class);

        try {
            $productId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $productId;
    }

    public function updateProduct(
        string $productId,
        string $name,
        string $amount,
        string $currency
    ): string
    {
        if ($currency === 'usd') $currency = Currency::USD;
            elseif ($currency === 'eur') $currency = Currency::EUR;
                else $currency = Currency::RUR;

        $command = new UpdateProductCommand(
            productId: OId::fromString($productId),
            name: $name,
            price: new Price($amount, $currency)
        );

        $handler = $this->service(UpdateProductHandler::class);

        try {
            $productId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $productId;
    }

    public function deleteProduct(
        string $productId
    ): string
    {
        $command = new DeleteProductCommand(
            productId: OId::fromString($productId)
        );

        $handler = $this->service(DeleteProductHandler::class);

        try {
            $productId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $productId;
    }

    public static function getSubscribedServices(): array
    {
        return [
            CreateSubscriptionHandler::class => CreateSubscriptionHandler::class,
            GeneratePaymentLinkFetcher::class => GeneratePaymentLinkFetcher::class,
            CreateCustomerHandler::class => CreateCustomerHandler::class,
            UpdateCustomerHandler::class => UpdateCustomerHandler::class,
            DeleteCustomerHandler::class => DeleteCustomerHandler::class,
            CreateProductHandler::class => CreateProductHandler::class,
            UpdateProductHandler::class => UpdateProductHandler::class,
            DeleteProductHandler::class => DeleteProductHandler::class,
        ];
    }

    /**
     * @template T of object
     * @param class-string<T> $service
     * @return T
     */
    private function service(string $service)
    {
        return $this->locator->get($service);
    }
}
