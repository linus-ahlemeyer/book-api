<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\Service\OpenLibraryDoc;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class OpenLibraryService
{
    public function __construct(private HttpClientInterface $openLibApi) {}

    /** @return OpenLibraryDoc[] */
    public function search(string $query, int $limit = 10): array
    {
        $resp = $this->openLibApi->request('GET', 'search.json', [
            'query' => [
                'q'      => preg_replace('/\s+/', ' ', trim($query)),
                'limit'  => $limit,
                'fields' => 'title,author_name,isbn',
            ],
            'timeout' => 3.0,
        ]);

        $data = $resp->toArray(false);
        $docs = (array)($data['docs'] ?? []);

        $out = [];
        foreach ($docs as $d) {
            $out[] = new OpenLibraryDoc(
                title: $d['title'] ?? null,
                authorNames: array_values((array)($d['author_name'] ?? [])),
                isbns: array_values((array)($d['isbn'] ?? [])),
            );
        }
        return $out;
    }
}
