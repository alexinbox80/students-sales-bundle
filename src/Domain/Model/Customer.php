<?php

namespace alexinbox80\StudentsSalesBundle\Domain\Model;

use alexinbox80\Shared\Domain\Events\EventsTrait;
use alexinbox80\Shared\Domain\Model\AggregateRootInterface;
use alexinbox80\Shared\Domain\Model\Email;
use alexinbox80\Shared\Domain\Model\Name;
use alexinbox80\Shared\Domain\Model\OId;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\CreatedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\UpdatedAtTrait;
use alexinbox80\StudentsSalesBundle\Domain\Model\Traits\DeletedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Агрегат "Клиент".
 * Не важен в контексте примера.
 * В реальном проекте необходимо будет добавить событийную обвязку для синхронизации Customer с User из основного монолита.
 * Источник истины для Customer - это User из основного монолита.
 * Поэтому необходимо:
 *  1) Добавить в User:$oid - универсальный идентификатор (авто-инкрементные здесь не подходят
 *  2) Добавить генерацию событий при CRUD операциях с User
 *  3) Добавить слушатели для этих событий в модуле Sales для синхронизации Customer с User
 *
 * В рамках примера мы этим не занимаемся.
 */
#[ORM\Entity]
#[ORM\Table(name: 'customers')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'consumer__email__uniq', fields: ['email'], options: ['where' => '(deleted_at IS NULL)'])]
class Customer implements AggregateRootInterface
{
    use CreatedAtTrait, UpdatedAtTrait, DeletedAtTrait;
    use EventsTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'shared__oid', unique: true)]
    private OId $id;

    #[ORM\Embedded(class: Name::class, columnPrefix: false)]
    private Name $name;

    #[ORM\Column(type:'string', length: 255, unique: true, nullable: false)]
    private Email $email;

    public function __construct(
        OId $id,
        Name $name,
        Email $email
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    public static function create(Name $name, Email $email): self
    {
        return new self(OId::next(), $name, $email);
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
