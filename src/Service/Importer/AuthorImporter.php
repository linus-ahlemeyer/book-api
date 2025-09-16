<?php

declare(strict_types=1);

namespace App\Service\Importer;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use App\Service\OpenLibraryService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class AuthorImporter
{
    public function __construct(
        private readonly OpenLibraryService $openLibrary,
        private readonly AuthorRepository $authors,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {}

    public function importByQuery(string $query, int $limit = 10): int
    {
        try {
            // returns OpenLibraryDoc[]
            $docs = $this->openLibrary->search($query, $limit);
        } catch (\Throwable $e) {
            $this->logger->error('OpenLibrary (authors) search failed', [
                'exception' => $e,
                'query' => $query,
            ]);
            return 0;
        }

        $needle = mb_strtolower(trim($query));
        $new = 0;
        $batchSeen = [];

        foreach ($docs as $doc) {
            $names = $doc->authorNames;
            if ($names === []) {
                continue;
            }

            // keep parity with local search (firstname|lastname LIKE %q%)
            $hay = mb_strtolower(implode(' ', $names));
            if ($needle !== '' && mb_strpos($hay, $needle) === false) {
                continue;
            }

            foreach ($names as $full) {
                [$first, $last] = $this->splitName($full);
                if ($first === '' && $last === '') {
                    continue;
                }

                $key = mb_strtolower($first.'|'.$last);
                if (isset($batchSeen[$key])) {
                    continue;
                }

                $exists = $this->authors->findOneBy([
                    'firstname' => $first,
                    'lastname'  => $last,
                ]);

                if ($exists) {
                    $batchSeen[$key] = true;
                    continue;
                }

                $this->em->persist(
                    (new Author())->setFirstname($first)->setLastname($last)
                );
                $batchSeen[$key] = true;
                $new++;
            }
        }

        if ($new > 0) {
            $this->em->flush();
        }

        return $new;
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
