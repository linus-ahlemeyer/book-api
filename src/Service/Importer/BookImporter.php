<?php

declare(strict_types=1);

namespace App\Service\Importer;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\OpenLibraryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class BookImporter
{
    public function __construct(
        private readonly OpenLibraryService $openLibrary,
        private readonly AuthorRepository $authors,
        private readonly BookRepository $books,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    public function importByQuery(string $query, int $limit = 10): int
    {
        try {
            // returns OpenLibraryDoc[]
            $docs = $this->openLibrary->search($query, $limit);
        } catch (\Throwable $e) {
            $this->logger->error('OpenLibrary (books) search failed', [
                'exception' => $e,
                'query' => $query,
            ]);
            return 0;
        }

        $needle = mb_strtolower(trim($query));
        $new = 0;

        foreach ($docs as $doc) {
            $title = $doc->title ?? null;
            $hay   = mb_strtolower((string) $title);

            // keep parity with local search (title LIKE %q%)
            if ($needle !== '' && mb_strpos($hay, $needle) === false) {
                continue;
            }

            // choose a sane ISBN (prefer 13, else 10)
            $isbn = $this->pickIsbn($doc->isbns);
            if ($isbn === null) {
                continue;
            }

            // skip if already present
            if ($this->books->findOneBy(['isbn' => $isbn])) {
                continue;
            }

            $authorName = $doc->authorNames[0] ?? null;
            if (!$authorName || !$title || $doc->publishYear === null) {
                continue;
            }

            $author = $this->findOrCreateAuthor($authorName);

            // Check for existing book by title and author
            $existingBook = $this->books->findOneBy(['title' => $title, 'author' => $author]);
            if ($existingBook) {
                continue;
            }

            $book = (new Book())
                ->setTitle($title)
                ->setIsbn($isbn)
                ->setAuthor($author)
                ->setPublicationYear($doc->publishYear);

            $this->em->persist($book);
            $new++;
        }

        if ($new > 0) {
            $this->em->flush();
        }

        return $new;
    }

    private function pickIsbn(array $rawList): ?string
    {
        $chosen10 = null;
        foreach ($rawList as $raw) {
            $norm = $this->normalizeIsbn($raw);
            if ($norm === null) {
                continue;
            }
            $len = strlen($norm);
            if ($len === 13) {
                return $norm; // prefer 13 immediately
            }
            if ($len === 10) {
                $chosen10 ??= $norm; // fallback if no 13 found
            }
        }
        return $chosen10;
    }

    private function normalizeIsbn(?string $isbn): ?string
    {
        if ($isbn === null) {
            return null;
        }
        $norm = strtoupper(str_replace(['-', ' '], '', $isbn));
        return $norm !== '' ? $norm : null;
    }

    private function findOrCreateAuthor(string $fullName): Author
    {
        [$first, $last] = $this->splitName($fullName);
        $existing = $this->authors->findOneBy(['firstname' => $first, 'lastname' => $last]);

        return $existing ?? (new Author())->setFirstname($first)->setLastname($last);
    }

    private function splitName(string $full): array
    {
        $full = trim($full);
        if ($full === '') {
            return ['', ''];
        }
        $parts = preg_split('/\s+/', $full, 2) ?: [];
        $first = trim($parts[0] ?? '');
        $last  = trim($parts[1] ?? '');
        return [$first, $last];
    }
}
