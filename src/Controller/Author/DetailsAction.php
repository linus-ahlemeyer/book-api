<?php

namespace App\Controller\Author;

use App\Entity\AbstractEntity;
use App\Entity\Author;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'author')]
#[Route(
    path: '/api/authors/{author}',
    name: 'details_author',
    methods: [Request::METHOD_GET]
)]
final class DetailsAction extends AbstractController
{
    public function __invoke(
        Author $author
    ): Response
    {
        return $this->json(
            data: $author,
            context: [
                'groups' => [AbstractEntity::GROUP_DETAILS,'author:details']
            ]);
    }
}
