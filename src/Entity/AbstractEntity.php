<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\MappedSuperclass]
abstract class AbstractEntity implements EntityInterface
{
    public const GROUP_DETAILS = 'details';
    public const GROUP_LIST = 'list';
    public const GROUP_CREATE = 'create';
    public const GROUP_UPDATE = 'update';

    #[Groups([self::GROUP_DETAILS, self::GROUP_LIST])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(nullable: false)]
    protected ?int $id = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
