<?php

declare(strict_types=1);

namespace App\DTO\Response\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Validator\Constraints as Assert;

class PaginationInfo
{
    #[Assert\Positive(message: 'Page number must be a positive integer.')]
    public int $page = 1;

    #[Assert\Range(
        notInRangeMessage: 'Limit must be between {{ min }} and {{ max }}.',
        min: 1,
        max: 100
    )]
    #[Assert\Positive(message: 'Limit must be a positive integer.')]
    public int $limit = 10;


    public function createPaginator(
        Query|QueryBuilder $query,
        bool $fetchJoinCollection = true
    ): Paginator {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        $query->setFirstResult(max(0, (($this->page - 1) * $this->limit) ));
        $query->setMaxResults(max(0, $this->limit));

        return new Paginator($query, $fetchJoinCollection);
    }
}
