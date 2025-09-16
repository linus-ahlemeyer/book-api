<?php

declare(strict_types=1);

use App\DTO\Response\PaginatedResponse;
use Symfony\Config\NelmioApiDocConfig;

return static function (NelmioApiDocConfig $config): void {
    $config
        ->useValidationGroups(true);

    $config
        ->models()
        ->useJms(false);

//    $config
//        ->areas('default')
//        ->disableDefaultRoutes(true);

    $config
        ->areas('default')
        ->pathPatterns([
            '^/api(?!/doc$)'
        ]);

    // Configure security.
    $config
        ->documentation('security', [
            ['Bearer' => null]
        ]);

    // Configure info
    // https://swagger.io/specification/#info-object
    $config
        ->documentation('info', [
            'title' => 'Book API',
            'description' => 'For internal use only',
            'version' => '1.0.0'//param('app.version')
        ]);

    // Configure components
    $config
        ->documentation('components', [
            'securitySchemes' => [
                'Bearer' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT'
                ]
            ]
        ]);

    // Model aliases.
    $modelAliases = $config->models();

    $modelAliases
        ->names()
        ->alias('PaginatedResponse')
        ->type(PaginatedResponse::class);

//    $modelAliases
//        ->names()
//        ->alias('BadRequestResponse')
//        ->type(BadRequestHttpException::class);
//
//    $modelAliases
//        ->names()
//        ->alias('NotFoundResponse')
//        ->type(NotFoundHttpException::class);
//
//    $modelAliases
//        ->names()
//        ->alias('AccessDeniedResponse')
//        ->type(AccessDeniedHttpException::class);
};
