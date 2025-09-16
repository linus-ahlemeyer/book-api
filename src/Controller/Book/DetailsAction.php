<?php

namespace App\Controller\Book;

use App\Entity\AbstractEntity;
use App\Entity\Book;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Tag(name: 'book')]
#[Route(path: '/api/books/{book}', name: 'details_book', methods: ['GET'])]
final class DetailsAction extends AbstractController
{
    public function __invoke(
        Book $book
    ): Response {
        return $this->json(
            data: $book,
            context: [
                'groups' => [AbstractEntity::GROUP_DETAILS,'book:details']
            ]);
    }
}
