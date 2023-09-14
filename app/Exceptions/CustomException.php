<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CustomException extends Exception
{
    protected $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function report(): void
    {
        // ...
    }


    public function render(): JsonResponse
    {
        return new JsonResponse('Error: ' . $this->message, 422);
    }
}
