<?php

namespace App\Controller\Book;

use App\Doctrine\QueryBuilder\SearchBuilder;
use App\DTO\Response\PaginatedResponse;
use App\DTO\Response\Query\OpenLibraryMode;
use App\DTO\Response\Query\PaginationInfo;
use App\DTO\Response\Query\SearchInfo;
use App\Entity\AbstractEntity;
use App\Messenger\SearchOpenLibraryBooksMessage;
use App\Repository\BookRepository;
use App\Service\Importer\BookImporter;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'book')]
#[Route(path: '/api/books', name: 'list_books', methods: ['GET'])]
final class ListAction extends AbstractController
{
    public function __invoke(
        #[MapQueryString] PaginationInfo $paginationInfo,
        #[MapQueryString] ?SearchInfo $searchInfo,
        #[MapQueryString] ?OpenLibraryMode $olMode,
        BookRepository $repository,
        MessageBusInterface $bus,
        BookImporter $bookImporter,
        #[MapQueryParameter] ?int $publicationYear = null,
    ): Response {
        $qb = $repository->createQueryBuilder('b');

        if ($searchInfo?->hasSearchTerm()) {
            if ($olMode?->isAsync()) {
                $bus->dispatch(new SearchOpenLibraryBooksMessage($searchInfo->search));
                return new JsonResponse([
                    'status' => 'queued',
                    'message' => 'Open Library enrichment has been scheduled',
                    'query' => $searchInfo->search,
                ], Response::HTTP_ACCEPTED);
            }

            if ($olMode?->isSync()) {
                $bookImporter->importByQuery($searchInfo->search);
            }

            $qb = (new SearchBuilder($qb))
                ->mapFields(['b.title'])
                ->build($searchInfo);
        }

        if ($publicationYear !== null) {
            $qb->andWhere('b.publicationYear = :publicationYear')
                ->setParameter('publicationYear', $publicationYear);
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
