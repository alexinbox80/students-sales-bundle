
1. composer require alexinbox80/students-sales-bundle
2. php bin/console doctrine:migrations:diff
3. php bin/console doctrine:migrations:migrate

```php
    use alexinbox80\StudentsSalesBundle\Presentation\Contract\SalesInterface;

    class Manager
    {
        public function __construct(
            private SalesInterface $sales,
        ) {
    }
```
```php
    public function subscribe(SubscribeDTO $subscribeDTO): SubscribedDTO
    {
        try {
            $subscriptionId = $this->sales->subscribe(
                $subscribeDTO->userId,
                $subscribeDTO->productId,
                new \DateTimeImmutable()
            );
        } catch (\Exception $e) {
            // TODO Handle
            throw new $e;
        }

        return new SubscribedDTO(
            true,
            $subscriptionId
        );
    }
```
```php
    public function generatePaymentLink(string $subscriptionId): GeneratedPaymentLinkDTO
    {
        try {
            return new GeneratedPaymentLinkDTO(
                true,
                $this->sales->generatePaymentLink($subscriptionId)
            );
        } catch (\Exception $e) {
            // TODO Handle exceptions
            throw $e;
        }
    }
```