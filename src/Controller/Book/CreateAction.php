<?php

namespace App\Controller\Book;

use App\DTO\Request\Book\Create\BookCreateRequest;
use App\Entity\AbstractEntity;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): Response {
        $data = $serializer->normalize($createRequest, 'json');
        $book = $serializer->denormalize($data, Book::class, 'json', [
            'groups' => [AbstractEntity::GROUP_CREATE]
        ]);

        $em->persist($book);
        $em->flush();

        return $this->json(
            data: ['id' => $book->getId()],
            status: Response::HTTP_CREATED,
            headers: ['Location' => sprintf('/api/books/%d', $book->getId())]
        );
    }
}
