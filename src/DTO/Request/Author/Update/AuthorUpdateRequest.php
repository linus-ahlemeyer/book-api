<?php

declare(strict_types=1);

namespace App\DTO\Request\Author\Update;

use App\Entity\AbstractEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class AuthorUpdateRequest
{
    #[Groups([AbstractEntity::GROUP_UPDATE])]
    #[Assert\NotBlank(groups: [AbstractEntity::GROUP_UPDATE])]
    public string $firstname;

    #[Groups([AbstractEntity::GROUP_UPDATE])]
    #[Assert\NotBlank(groups: [AbstractEntity::GROUP_UPDATE])]
    public string $lastname;
}