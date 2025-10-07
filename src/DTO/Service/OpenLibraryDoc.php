<?php
declare(strict_types=1);

namespace App\DTO\Service;

final class OpenLibraryDoc
{
    public function __construct(
        public readonly ?string $title,
        /** @var string[] */
        public readonly array $authorNames,
        /** @var string[] */
        public readonly array $isbns,
        public readonly ?int $publishYear
    ) {}
}
