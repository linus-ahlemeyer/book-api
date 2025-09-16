<?php

namespace App\Controller\Author;

use App\DTO\Request\Author\Update\AuthorUpdateRequest;
use App\Entity\AbstractEntity;
use App\Entity\Author;
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

#[Tag(name: 'author')]
#[Route(
    path: '/api/authors/{author}',
    name: 'update_authors',
    methods: [Request::METHOD_PUT]
)]
final class UpdateAction extends AbstractController
{
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: AuthorUpdateRequest::class))
    )]
    public function __invoke(
        Author $author,
        #[MapRequestPayload(
            validationGroups: [AbstractEntity::GROUP_UPDATE]
        )] AuthorUpdateRequest $payload,
        SerializerInterface $serializer,
        EntityManagerInterface $em
    ): Response {
        $data = $serializer->normalize($payload, 'json');

        $serializer->denormalize(
            data: $data,
            type: Author::class,
            context: [
                AbstractNormalizer::OBJECT_TO_POPULATE => $author,
                AbstractNormalizer::GROUPS => [AbstractEntity::GROUP_UPDATE],
            ]
        );

        $em->flush();
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
