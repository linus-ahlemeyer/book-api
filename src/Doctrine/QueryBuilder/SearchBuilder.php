<?php

declare(strict_types=1);

namespace App\Doctrine\QueryBuilder;

use App\DTO\Response\Query\SearchInfo;
use Doctrine\ORM\QueryBuilder;

/**
 * Utility class to add search conditions to a Doctrine QueryBuilder
 * based on a simple SearchInfo DTO.
 */
class SearchBuilder
{
    /** @var array<string> List of database field aliases to search within */
    private array $searchableFields = [];

    /**
     * @param QueryBuilder $qb The initial QueryBuilder instance.
     */
    public function __construct(private readonly QueryBuilder $qb)
    {
    }

    /**
     * Maps a single database field (with alias) to be included in the search.
     *
     * @param string $dbFieldAlias e.g., 'a.firstname', 'p.name'
     * @return self
     */
    public function mapField(string $dbFieldAlias): self
    {
        if (!in_array($dbFieldAlias, $this->searchableFields, true)) {
            $this->searchableFields[] = $dbFieldAlias;
        }
        return $this;
    }

    /**
     * Maps multiple database fields (with aliases) to be included in the search.
     *
     * @param array<string> $dbFieldAliases e.g., ['a.firstname', 'a.lastname']
     * @return self
     */
    public function mapFields(array $dbFieldAliases): self
    {
        foreach ($dbFieldAliases as $dbFieldAlias) {
            $this->mapField($dbFieldAlias);
        }
        return $this;
    }

    /**
     * Applies the search logic to the QueryBuilder based on the mapped fields
     * and the provided SearchRequest.
     *
     * Uses case-insensitive partial matching (LIKE %term%).
     *
     * @param SearchInfo $searchInfo The DTO containing the search term.
     * @return QueryBuilder The modified (or original if no search applied) QueryBuilder instance.
     */
    public function build(SearchInfo $searchInfo): QueryBuilder
    {
        if (!$searchInfo->hasSearchTerm() || empty($this->searchableFields)) {
            return $this->qb;
        }

        $searchTerm = trim($searchInfo->search);
        $searchParam = '%' . mb_strtolower($searchTerm, 'UTF-8') . '%';

        $orX = $this->qb->expr()->orX();
        foreach ($this->searchableFields as $field) {
            // Add condition for each mapped field using LOWER() and LIKE
            $orX->add($this->qb->expr()->like('LOWER(' . $field . ')', ':search'));
        }

        $this->qb->andWhere($orX)
            ->setParameter('search', $searchParam);

        return $this->qb;
    }
}
