<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model;

use alexinbox80\Shared\Domain\Events\EventsTrait;
use alexinbox80\Shared\Domain\Model\AggregateRootInterface;
use alexinbox80\Shared\Domain\Model\OId;
use Webmozart\Assert\Assert;
use Doctrine\ORM\Mapping as ORM;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\CreatedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\UpdatedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\DeletedAtTrait;

/**
 * Агрегат "Продукт".
 * Продукт на который подписывается пользователь.
 * Не важен в контексте примера.
 */
#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'product__name__uniq', fields: ['name'], options: ['where' => '(deleted_at IS NULL)'])]
class Product implements AggregateRootInterface
{
    use CreatedAtTrait, UpdatedAtTrait, DeletedAtTrait;
    use EventsTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'shared__oid', unique: true)]
    private OId $id;

    #[ORM\Column(type:'string', length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\Embedded(class: Price::class, columnPrefix: false)]
    private Price $price;

    public function __construct(OId $id, string $name, Price $price)
    {
        Assert::stringNotEmpty($name);

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    public static function create(string $name, Price $price): self
    {
        return new self(OId::next(), $name, $price);
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }


    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updatePrice(Price $price): void
    {
        $this->price = $price;
    }
}
