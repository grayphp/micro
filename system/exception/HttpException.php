<?php

declare(strict_types=1);

namespace system\exception;

use RuntimeException;
use Throwable;

/**
 * Base HTTP exception.
 *
 * Carry an HTTP status code alongside the message so that
 * the global exception handler can send the correct response.
 */
class HttpException extends RuntimeException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message ?: $this->defaultMessage(), $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    private function defaultMessage(): string
    {
        return match ($this->statusCode) {
            400     => 'Bad Request',
            401     => 'Unauthorized',
            403     => 'Forbidden',
            404     => 'Not Found',
            405     => 'Method Not Allowed',
            419     => 'CSRF Token Mismatch',
            422     => 'Unprocessable Entity',
            429     => 'Too Many Requests',
            500     => 'Internal Server Error',
            503     => 'Service Unavailable',
            default => 'HTTP Error',
        };
    }
}
