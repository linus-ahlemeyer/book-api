<?php

namespace App\Controller\Author;

use App\Entity\AbstractEntity;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'author')]
#[Route(path: '/api/authors', name: 'create_author', methods: ['POST'])]
final class CreateAction extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        #[MapRequestPayload(
            acceptFormat: 'json',
            serializationContext: [
                'groups' => [AbstractEntity::GROUP_CREATE],
                'allow_extra_attributes' => false,
            ],
            validationGroups: [AbstractEntity::GROUP_CREATE]
        )]
        Author $author
    ): Response {
        $em->persist($author);
        $em->flush();

        return $this->json(
            data: ['id' => $author->getId()],
            status: Response::HTTP_CREATED,
            headers: ['Location' => sprintf('/api/authors/%d', $author->getId())]
        );
    }
}
