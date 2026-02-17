<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ErrorHandler\Communication\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @method \Spryker\Zed\ErrorHandler\Communication\ErrorHandlerCommunicationFactory getFactory()
 * @method \Spryker\Zed\ErrorHandler\ErrorHandlerConfig getConfig()
 */
class ErrorPageEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * @var int
     */
    protected const PRIORITY = 50;

    /**
     * {@inheritDoc}
     * - Adds a listener for the {@link \Symfony\Component\HttpKernel\KernelEvents::EXCEPTION} event.
     * - Executes {@link \SprykerShop\Yves\ErrorPageExtension\Dependency\Plugin\ExceptionHandlerPluginInterface} which is able to handle the current status code.
     *
     * @api
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addListener(KernelEvents::EXCEPTION, function (ExceptionEvent $event) {
            $this->onKernelException($event);
        }, static::PRIORITY);

        return $eventDispatcher;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     *
     * @return void
     */
    protected function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $exceptionHandlerStrategyPlugins = $this->getFactory()->getExceptionHandlerStrategyPlugins();
        foreach ($exceptionHandlerStrategyPlugins as $exceptionHandlerStrategyPlugin) {
            if (!$exceptionHandlerStrategyPlugin->canHandle($exception)) {
                continue;
            }

            $response = $exceptionHandlerStrategyPlugin->handleException(FlattenException::createFromThrowable($exception));

            $event->setResponse($response);
            $event->stopPropagation();

            break;
        }

        if (!$event->hasResponse()) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getStatusCode();
            }
            if (
                $statusCode === Response::HTTP_INTERNAL_SERVER_ERROR ||
                $statusCode === Response::HTTP_NOT_FOUND ||
                $statusCode === Response::HTTP_FORBIDDEN
            ) {
                return;
            }
            if ($statusCode >= 400 && isset(Response::$statusTexts[$statusCode])) {
                $response = new Response($statusCode . ' ' . Response::$statusTexts[$statusCode], $statusCode);
                $event->setResponse($response);
                $event->stopPropagation();
            }
        }
    }
}
