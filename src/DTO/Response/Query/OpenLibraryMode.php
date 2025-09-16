<?php

declare(strict_types=1);

namespace App\DTO\Response\Query;

use Nelmio\ApiDocBundle\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class OpenLibraryMode
{
    #[Assert\Choice(
        choices: ['none', 'sync', 'async'],
        message: 'Choose a valid mode: none, sync, or async.'
    )]
    public string $ol = 'none';

    #[Ignore]
    public function isNone(): bool
    {
        return $this->ol === 'none';
    }

    #[Ignore]
    public function isSync(): bool
    {
        return $this->ol === 'sync';
    }

    #[Ignore]
    public function isAsync(): bool
    {
        return $this->ol === 'async';
    }
}