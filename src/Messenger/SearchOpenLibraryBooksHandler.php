<?php

namespace App\Messenger;
use App\Service\Importer\BookImporter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SearchOpenLibraryBooksHandler {
    public function __construct(private BookImporter $importer) {}
    public function __invoke(SearchOpenLibraryBooksMessage $m): void {
        $this->importer->importByQuery($m->query);
    }
}