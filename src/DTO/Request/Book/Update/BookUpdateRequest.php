<?php

declare(strict_types=1);

namespace App\DTO\Request\Book\Update;

use App\Entity\AbstractEntity;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema]
class BookUpdateRequest
{
    #[OA\Property(type: 'string', example: 'New Title')]
    #[Groups([AbstractEntity::GROUP_UPDATE])]
    #[Assert\NotBlank(groups: [AbstractEntity::GROUP_UPDATE])]
    public string $title;

    #[OA\Property(type: 'string', example: '9780141036137')]
    #[Groups([AbstractEntity::GROUP_UPDATE])]
    #[Assert\NotBlank(groups: [AbstractEntity::GROUP_UPDATE])]
    public string $isbn;

    // allow changing author by id (AuthorIdDenormalizer will resolve it)
    #[OA\Property(type: 'integer', format: 'int64', example: 309, writeOnly: true, nullable: true)]
    #[Groups([AbstractEntity::GROUP_UPDATE])]
    public ?int $author = null;

    #[OA\Property(type: 'integer', format: 'int64', example: 2012, nullable: false)]
    #[Groups([AbstractEntity::GROUP_UPDATE])]
    #[Assert\NotBlank(groups: [AbstractEntity::GROUP_UPDATE])]
    public int $publicationYear;
}