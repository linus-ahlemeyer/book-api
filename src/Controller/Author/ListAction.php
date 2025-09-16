<?php

namespace App\Controller\Author;

use App\Doctrine\QueryBuilder\SearchBuilder;
use App\DTO\Response\PaginatedResponse;
use App\DTO\Response\Query\OpenLibraryMode;
use App\DTO\Response\Query\PaginationInfo;
use App\DTO\Response\Query\SearchInfo;
use App\Entity\AbstractEntity;
use App\Messenger\SearchOpenLibraryAuthorsMessage;
use App\Repository\AuthorRepository;
use App\Service\Importer\AuthorImporter;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'author')]
#[Route(
    path: '/api/authors',
    name: 'list_authors',
    methods: [Request::METHOD_GET]
)]
final class ListAction extends AbstractController
{
    public function __invoke(
        #[MapQueryString] PaginationInfo $paginationInfo,
        #[MapQueryString] ?SearchInfo $searchInfo,
        #[MapQueryString] ?OpenLibraryMode $olMode,
        AuthorRepository $repository,
        MessageBusInterface $bus,
        AuthorImporter $authorImporter
    ): Response {
        $qb = $repository->createQueryBuilder('a');

        if ($searchInfo?->hasSearchTerm()) {
            if ($olMode?->isAsync()) {
                $bus->dispatch(new SearchOpenLibraryAuthorsMessage($searchInfo->search));
                return new JsonResponse([
                    'status' => 'queued',
                    'message' => 'Open Library enrichment has been scheduled',
                    'query' => $searchInfo->search,
                ], Response::HTTP_ACCEPTED);
            }

            if ($olMode?->isSync()) {
                $authorImporter->importByQuery($searchInfo->search);
            }

            $qb = (new SearchBuilder($qb))
                ->mapFields(['a.firstname', 'a.lastname'])
                ->build($searchInfo);
        }

        $paginator = $paginationInfo->createPaginator($qb);

        return $this->json(
            new PaginatedResponse(
                items: $paginator,
                totalItems: count($paginator),
                page: $paginationInfo->page,
                perPage: $paginationInfo->limit
            ),
            context: [
                'groups' => AbstractEntity::GROUP_LIST
            ]
        );
    }
}
