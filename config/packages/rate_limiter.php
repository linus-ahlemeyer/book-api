<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->rateLimiter()
        ->limiter('anonymous_api')
        // use 'sliding_window' if you prefer that policy
        ->policy('fixed_window')
        ->limit(100)
        ->interval('60 minutes')
    ;

    $framework->rateLimiter()
        ->limiter('authenticated_api')
        ->policy('token_bucket')
        ->limit(5000)
        ->rate()
        ->interval('15 minutes')
        ->amount(500)
    ;
};
