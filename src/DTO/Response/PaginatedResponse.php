<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\AbstractEntity;
use App\Entity\EntityInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[OA\Schema(
    schema: "PaginatedResponse",
    properties: [
        new OA\Property(property: "totalItems", type: "integer"),
        new OA\Property(property: "page", type: "integer"),
        new OA\Property(property: "perPage", type: "integer"),
        new OA\Property(property: "totalPages", type: "integer"),
        new OA\Property(property: "items", type: "array", items: new OA\Items(type: "object"))
    ]
)]
class PaginatedResponse
{
    #[Groups(AbstractEntity::GROUP_LIST)]
    public int $totalItems;
    #[Groups(AbstractEntity::GROUP_LIST)]
    public int $page;
    #[Groups(AbstractEntity::GROUP_LIST)]
    public int $perPage;
    #[Groups(AbstractEntity::GROUP_LIST)]
    public int $totalPages;

    #[Groups(AbstractEntity::GROUP_LIST)]
    /** @var iterable<EntityInterface> */
    public iterable $items;

    public function __construct(
        iterable $items,
        int $totalItems = 0,
        int $page = 1,
        int $perPage = 10
    ) {
        $this->items = $items;
        $this->totalItems = max(0, $totalItems);
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);

        $this->totalPages = $this->perPage > 0
            ? (int) ceil($this->totalItems / $this->perPage)
            : 0;
    }
}
