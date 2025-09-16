<?php

namespace App\Controller\Book;

use App\DTO\Request\Book\Create\BookCreateRequest;
use App\Entity\AbstractEntity;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'book')]
#[Route(
    path: '/api/books',
    name: 'create_book',
    methods: [Request::METHOD_POST]
)]
final class CreateAction extends AbstractController
{
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: BookCreateRequest::class))
    )]
    public function __invoke(
        #[MapRequestPayload(
            acceptFormat: 'json',
            serializationContext: ['allow_extra_attributes' => false]
        )] BookCreateRequest $createRequest,
        AuthorRepository $authorRepository,
        DenormalizerInterface $denormalizer,
        EntityManagerInterface $em
    ): Response {
        $book = $denormalizer->denormalize(
            data:  $createRequest,
            type: Book::class,
            context: [
                'groups' => [AbstractEntity::GROUP_CREATE]
            ]
        );

        $author = $authorRepository->find($createRequest->author);
        if (!$author) {
            return $this->json(['message' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $book->setAuthor($author);
        $em->persist($book);
        $em->flush();

        return $this->json(
            data: ['id' => $book->getId()],
            status: Response::HTTP_CREATED,
            headers: ['Location' => sprintf('/api/books/%d', $book->getId())]
        );
    }
}
