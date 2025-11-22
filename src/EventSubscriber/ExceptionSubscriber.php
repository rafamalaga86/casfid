<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests (e.g., requests to /api/)
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = new JsonResponse();
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'An unexpected error occurred.';
        $errors = [];

        // Start with generic HTTP exception handling
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        // If it's a validation error, override the message and extract details
        if (($exception instanceof BadRequestHttpException && $exception->getPrevious() instanceof ValidationException) || $exception instanceof ValidationException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422
            $message = 'Validation failed.'; // Explicitly set generic message
            $validationException = ($exception instanceof BadRequestHttpException) ? $exception->getPrevious() : $exception;

            foreach ($validationException->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
        }
        // ... handle other generic exceptions if needed

        $response->setData([
            'message' => $message,
            'errors' => $errors,
        ]);
        $response->setStatusCode($statusCode);
        $response->setEncodingOptions(
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
