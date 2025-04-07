<?php

namespace alexinbox80\StudentsSalesBundle\Presentation\Contract;

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
}
