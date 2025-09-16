<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: "book")]
#[UniqueEntity(fields: ['isbn'], message: 'This ISBN already exists.')]
class Book extends AbstractEntity
{
    #[Groups([self::GROUP_DETAILS, self::GROUP_LIST, self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[ORM\Column(nullable: false)]
    #[Assert\Length(max: 255, groups: [self::GROUP_CREATE, self::GROUP_UPDATE])]
    private string $title;

    #[Groups([self::GROUP_DETAILS, self::GROUP_LIST, self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[ORM\Column(unique: true, nullable: false)]
    #[Assert\Length(max: 255, groups: [self::GROUP_CREATE, self::GROUP_UPDATE])]
    private string $isbn;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'books')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['book:details', self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[Assert\NotNull(groups: [AbstractEntity::GROUP_CREATE])]
    private ?Author $author = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
