<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Only format API routes
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $e = $event->getThrowable();
        $status = 500;
        $payload = ['message' => 'Internal Server Error'];

        if ($e instanceof HttpExceptionInterface
            && $e->getStatusCode() === 422
            && $e->getPrevious() instanceof ValidationFailedException
        ) {
            $status = 422;
            $payload = ['message' => 'Validation failed', 'errors' => []];
            foreach ($e->getPrevious()->getViolations() as $v) {
                $payload['errors'][$v->getPropertyPath()][] = $v->getMessage();
            }
            $event->setResponse(new JsonResponse($payload, $status));
            return;
        }

        // HTTP exceptions (404, 403, 400, ...)
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $payload['message'] = $e->getMessage() !== '' ? $e->getMessage() : $this->defaultMessage($status);
        }
        // Validation (MapRequestPayload, Assert, etc.)
        elseif ($e instanceof ValidationFailedException) {
            $status = 422;
            $payload['message'] = 'Validation failed';
            $payload['errors'] = [];
            foreach ($e->getViolations() as $violation) {
                $payload['errors'][$violation->getPropertyPath()][] = $violation->getMessage();
            }
        }
        // Unique constraint (e.g., duplicate ISBN)
        elseif ($e instanceof UniqueConstraintViolationException) {
            $status = 409;
            $payload['message'] = 'Conflict: unique constraint violated';
        }
        // Bad JSON / wrong content-type body
        elseif ($e instanceof NotEncodableValueException) {
            $status = 400;
            $payload['message'] = 'Invalid request body';
        }

        // Serializer type/denormalization errors (e.g. author must be int)
        elseif ($e instanceof NotNormalizableValueException) {
            $status = 400;
            $payload['message'] = 'Invalid data';
            $payload['errors'] = ['detail' => [$e->getMessage()]];
        }

        // FK constraint conflicts
        elseif ($e instanceof ForeignKeyConstraintViolationException) {
            $status = 409;
            $payload['message'] = 'Conflict: foreign key constraint violated';
        }

        // Extra attributes in request body when deserializing with allow_extra_attributes = false
        elseif ($e instanceof ExtraAttributesException) {
            $status = 400;
            $payload['message'] = 'Invalid request body';
            $payload['errors'] = ['extra' => $e->getExtraAttributes()];
        }

        // MySQL truncation / data too long often uses SQLSTATE 22001
        elseif ($e instanceof DriverException) {
            if ($e->getSQLState() === '22001') {
                $status = 422;
                $payload['message'] = 'Validation failed';
                $payload['errors'] = ['detail' => ['One or more fields exceed allowed length.']];
            } else {
                // Other DB errors
                $status = 500;
                $payload['message'] = 'Database error';
                $payload['errors'] = ['detail' => [$e->getMessage()]];
            }
        }

        $event->setResponse(new JsonResponse($payload, $status));
    }

    private function defaultMessage(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            default => 'Error',
        };
    }
}
