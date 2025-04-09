<?php

namespace alexinbox80\StudentsSalesBundle\Presentation\Contract;

use alexinbox80\Shared\Domain\Model\OId;
use DateTimeImmutable;

/**
 * Контракт или фасад для прямого доступа из кода монолита к модулю Sales.
 * Этот же контракт может быть использован для реализации HTTP API клиента для модуля Sales.
 *
 * Обратите внимание - на входе и выходе только примитивные типы PHP,
 * либо могут быть DTO-шки, которые будут частью контракта.
 */
interface SalesInterface
{
    /**
     * @return string Subscription id
     */
    public function subscribe(
        string $customerId,
        string $productId,
        DateTimeImmutable $startDate,
    ): string;

    public function generatePaymentLink(string $subscriptionId): string;

    /**
     * @return string Customer id
     */
    public function createCustomer(
        string $customerId,
        string $firstName,
        string $lastName,
        string $email,
    ): string;

    /**
     * @return string Customer id
     */
    public function updateCustomer(
        string $customerId,
        string $firstName,
        string $lastName,
        string $email,
    ): string;

    /**
     * @return string Customer id
     */
    public function deleteCustomer(
        string $customerId
    ): string;

    /**
     * @return string Product id
     */
    public function createProduct(
        string $name,
        string $amount,
        string $currency,
    ): string;

    /**
     * @return string Product id
     */
    public function updateProduct(
        string $productId,
        string $name,
        string $amount,
        string $currency,
    ): string;

    /**
     * @return string Product id
     */
    public function deleteProduct(
        string $productId
    ): string;

    /**
     * @return string Invoice id
     */
    public function createInvoice(
        string $subscriptionId,
        DateTimeImmutable $dueDate
    ): string;

    /**
     * @return string Invoice id
     */
    public function expireInvoice(
        string $invoiceId
    ): void;
}
