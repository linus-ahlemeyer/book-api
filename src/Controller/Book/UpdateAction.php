<?php

namespace App\Controller\Book;

use App\DTO\Request\Book\Update\BookUpdateRequest;
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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Tag(name: 'book')]
#[Route(
    path: '/api/books/{book}',
    name: 'update_books',
    methods: [Request::METHOD_PUT]
)]
final class UpdateAction extends AbstractController
{
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: BookUpdateRequest::class))
    )]
    public function __invoke(
        Book $book,
        #[MapRequestPayload(
            acceptFormat: 'json',
            serializationContext: [
                'allow_extra_attributes' => false
            ],
            validationGroups: [AbstractEntity::GROUP_UPDATE]
        )] BookUpdateRequest $payload,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): Response {

        $data = $serializer->normalize($payload, 'json');
        $serializer->denormalize($data, Book::class, context: [
            AbstractNormalizer::OBJECT_TO_POPULATE => $book,
            AbstractNormalizer::GROUPS => [AbstractEntity::GROUP_UPDATE],
        ]);

        $em->flush();
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
