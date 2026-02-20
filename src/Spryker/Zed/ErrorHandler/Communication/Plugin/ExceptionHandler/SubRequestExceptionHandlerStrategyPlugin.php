<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ErrorHandler\Communication\Plugin\ExceptionHandler;

use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\ErrorHandlerExtension\Dependency\Plugin\ExceptionHandlerStrategyPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * @method \Spryker\Zed\ErrorHandler\Communication\ErrorHandlerCommunicationFactory getFactory()
 * @method \Spryker\Zed\ErrorHandler\ErrorHandlerConfig getConfig()
 */
class SubRequestExceptionHandlerStrategyPlugin extends AbstractPlugin implements ExceptionHandlerStrategyPluginInterface
{
    use LoggerTrait;

    /**
     * {@inheritDoc}
     * - Checks if the exception can be handled using statusCode, which we compare with the list of valid status codes.
     *
     * @api
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    public function canHandle(Throwable $exception): bool
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        return in_array($statusCode, $this->getConfig()->getValidSubRequestExceptionStatusCodes(), true);
    }

    /**
     * {@inheritDoc}
     * - Renders error page directly using Twig instead of creating a sub-request.
     * - Provides safe fallback HTML if template rendering fails completely.
     *
     * @api
     *
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleException(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();
        $templateName = sprintf('@ErrorHandler/Error/error%d.twig', $statusCode);

        $variables = [
            'error' => $exception->getMessage(),
            'errorCode' => $statusCode,
            'hideUserMenu' => true,
        ];

        try {
            $content = $this->getFactory()->getTwig()->render($templateName, $variables);
        } catch (Throwable $e) {
            $this->getLogger()->error($e->getMessage(), ['exception' => $e]);
            $content = $this->renderSafeHtmlFallback($statusCode, $variables['error']);
        }

        return new Response($content, $statusCode);
    }

    protected function renderSafeHtmlFallback(int $statusCode, ?string $errorMessage): string
    {
        $safeErrorMessage = htmlspecialchars($errorMessage ?? 'An error occurred', ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error %d</title>
    <style>
        body { font-family: sans-serif; margin: 50px; text-align: center; }
        h1 { color: #333; }
        p { color: #666; }
    </style>
</head>
<body>
    <h1>Error %d</h1>
    <p>%s</p>
</body>
</html>',
            $statusCode,
            $statusCode,
            $safeErrorMessage,
        );
    }
}
