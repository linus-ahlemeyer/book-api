<?php

namespace App\Messenger;
use App\Service\Importer\AuthorImporter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SearchOpenLibraryAuthorsHandler {
    public function __construct(private AuthorImporter $importer) {}
    public function __invoke(SearchOpenLibraryAuthorsMessage $m): void {
        $this->importer->importByQuery($m->query);
    }
}