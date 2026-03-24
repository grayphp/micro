<?php

declare(strict_types=1);

namespace system\exception;

/**
 * Thrown when request validation fails.
 *
 * @example
 *   $errors = $this->request()->validate(['email' => 'required|email']);
 *   if ($errors) {
 *       throw new ValidationException($errors);
 *   }
 */
class ValidationException extends HttpException
{
    /** @param array<string, list<string>> $errors */
    public function __construct(private readonly array $errors, string $message = 'Validation Failed')
    {
        parent::__construct(422, $message);
    }

    /** @return array<string, list<string>> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
