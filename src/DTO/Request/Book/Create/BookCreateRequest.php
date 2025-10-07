<?php

namespace App\DTO\Request\Book\Create;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    required: ['title', 'isbn', 'author']
)]
class BookCreateRequest
{
    #[OA\Property(type: 'string', maxLength: 255, example: 'The Old Man and the Sea')]
    #[Assert\NotBlank] #[Assert\Length(max: 255)]
    public string $title;

    #[OA\Property(type: 'string', maxLength: 255, example: '9780684801223')]
    #[Assert\NotBlank] #[Assert\Length(max: 255)]
    public string $isbn;

    #[OA\Property(type: 'integer', format: 'int64', example: 309, writeOnly: true)]
    #[Assert\NotBlank]
    public int $author;

    #[OA\Property(type: 'integer', format: 'int64', example: 1967)]
    #[Assert\NotBlank]
    public int $publicationYear;
}