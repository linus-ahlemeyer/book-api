<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->secret('%env(APP_SECRET)%');
    $framework->session()->enabled(true);

    $framework->httpClient()
        ->scopedClient('openLibApi')
            ->baseUri('https://openlibrary.org/')
            ->header('Accept', 'application/json');

    if ($_ENV['APP_ENV'] === 'test') {
        $framework->test(true);
        $framework->session()->storageFactoryId('session.storage.factory.mock_file');
    }
};
