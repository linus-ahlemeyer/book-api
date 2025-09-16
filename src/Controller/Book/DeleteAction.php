<?php

namespace App\Controller\Book;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'book')]
#[Route(
    path: '/api/books/{book}',
    name: 'delete_books',
    methods: [Request::METHOD_DELETE]
)]
final class DeleteAction  extends AbstractController
{
    public function __invoke(
        Book $book,
        EntityManagerInterface $em
    ): Response {
        $em->remove($book);
        $em->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
