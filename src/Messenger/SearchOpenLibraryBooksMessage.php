<?php

namespace App\Messenger;
final readonly class SearchOpenLibraryBooksMessage { public function __construct(public string $query) {} }