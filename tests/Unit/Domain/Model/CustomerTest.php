<?php

namespace alexinbox80\StudentsSalesBundle\Tests\Unit\Domain\Model;

use alexinbox80\StudentsSalesBundle\Domain\Model\Customer;
use alexinbox80\Shared\Domain\Model\Email;
use alexinbox80\Shared\Domain\Model\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Customer::class)]
class CustomerTest extends TestCase
{
    private Customer $customer;
    private Name $name;
    private Email $email;

    protected function setUp(): void
    {
        $this->name = new Name('John', 'Doe');
        $this->email = new Email('john.doe@example.com');
        $this->customer = Customer::create($this->name, $this->email);
    }

    public function testCreateCustomerGeneratesId(): void
    {
        self::assertNotNull($this->customer->getId());
    }

    public function testCustomerHasCorrectName(): void
    {
        self::assertTrue($this->customer->getName()->isEqual($this->name));
        self::assertSame('John', $this->customer->getName()->getFirst());
        self::assertSame('Doe', $this->customer->getName()->getLast());
    }

    public function testCustomerHasCorrectEmail(): void
    {
        self::assertTrue($this->customer->getEmail()->isEqual($this->email));
        self::assertSame('john.doe@example.com', $this->customer->getEmail()->toString());
    }
}
