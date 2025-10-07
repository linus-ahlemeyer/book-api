<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\Table(name: "author")]
class Author extends AbstractEntity
{
    #[Groups([self::GROUP_DETAILS, self::GROUP_LIST, self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[ORM\Column(nullable: false)]
    #[Assert\NotBlank(message: 'First name cannot be blank', groups: [self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[Assert\Length(max: 255, groups: [self::GROUP_CREATE, self::GROUP_UPDATE])]
    private string $firstname;

    #[Groups([self::GROUP_DETAILS, self::GROUP_LIST, self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[ORM\Column(nullable: false)]
    #[Assert\NotBlank(message: 'Last name cannot be blank', groups: [self::GROUP_CREATE, self::GROUP_UPDATE])]
    #[Assert\Length(max: 255, groups: [self::GROUP_CREATE, self::GROUP_UPDATE])]
    private string $lastname;

    /**
     * @var Collection<array-key, Book>|Selectable
     */
    #[Groups(['author:details'])]
    #[ORM\OneToMany(
        targetEntity: Book::class,
        mappedBy: 'author',
        cascade:[
            'persist',
            'remove'
        ],
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true
    )]
    private Collection|Selectable $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return ArrayCollection<array-key, Book>|Book[]
     */
    public function getBooks(): Collection|Selectable
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if ($this->books->contains($book) === false) {
            $this->books->add($book);
            $book->setAuthor($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        $this->books->removeElement($book);

        return $this;
    }

    /**
     * @param array $books
     * @return Author
     */
    public function setBooks(array $books): self
    {
        $this->books = new ArrayCollection();
        foreach ($books as $book) {
            $this->addBook($book);
        }

        return $this;
    }
}
