<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class LoginException extends Exception
{
    protected $code = 422;
    protected $message = 'The provided credentials do not match our records.';

    public function report(): void
    {
        //
    }

    public function render($request)
    {
        return new JsonResponse([
            'errors' => [
                'message' => $this->message,
            ]
        ], $this->code);
    }
}
