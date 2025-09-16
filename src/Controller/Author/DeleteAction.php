<?php

namespace App\Controller\Author;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'author')]
#[Route(
    path: '/api/authors/{author}',
    name: 'delete_authors',
    methods: [Request::METHOD_DELETE]
)]
final class DeleteAction  extends AbstractController
{
    public function __invoke(Author $author, EntityManagerInterface $em): Response
    {
        $em->remove($author);
        $em->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
