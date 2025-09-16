<?php

namespace App\DTO\Response\Query;

use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class SearchInfo
{
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 255)]
    public string $search;

    #[Ignore]
    public function hasSearchTerm(): bool
    {
        return isset($this->search) && trim($this->search) !== '';
    }
}
