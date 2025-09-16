<?php
namespace App\Serializer;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class AuthorIdDenormalizer implements DenormalizerInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Author::class && (\is_int($data) || (\is_string($data) && ctype_digit($data)));
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = []): Author
    {
        $id = (int) $data;

        $author = $this->em->getRepository(Author::class)->find($id);
        if (!$author) {
            throw new NotNormalizableValueException(sprintf('Author with id %d not found.', $id));
        }

        return $author;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Author::class => false
        ];
    }
}
