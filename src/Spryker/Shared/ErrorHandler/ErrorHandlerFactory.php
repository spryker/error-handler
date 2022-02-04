<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\ErrorHandler;

use Exception;
use Spryker\Service\Kernel\Locator;
use Spryker\Service\UtilSanitize\UtilSanitizeServiceInterface;
use Spryker\Shared\Config\Config;
use Spryker\Shared\ErrorHandler\ErrorRenderer\ApiErrorRenderer;
use Spryker\Shared\ErrorHandler\ErrorRenderer\CliErrorRenderer;
use Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Spryker\Shared\ErrorHandler\ErrorRenderer\WebHtmlErrorRenderer;

class ErrorHandlerFactory
{
    /**
     * @var string
     */
    public const APPLICATION_ZED = 'ZED';

    /**
     * @var string
     */
    public const APPLICATION_GLUE = 'GLUE';

    /**
     * @var string
     */
    public const SAPI_CLI = 'cli';

    /**
     * @var string
     */
    public const SAPI_PHPDBG = 'phpdbg';

    /**
     * @var string
     */
    protected $application;

    /**
     * @param string $application
     */
    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     * @return \Spryker\Shared\ErrorHandler\ErrorHandler
     */
    public function createErrorHandler()
    {
        $errorHandler = new ErrorHandler(
            ErrorLogger::getInstance(),
            $this->createErrorRenderer(),
            $this->getUtilSanitizeService(),
        );

        return $errorHandler;
    }

    /**
     * @return \Spryker\Shared\ErrorHandler\ErrorLogger
     */
    protected function createErrorLogger()
    {
        return new ErrorLogger();
    }

    /**
     * @return \Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface
     */
    protected function createErrorRenderer()
    {
        if ($this->isGlueApplication()) {
            $errorRendererClassName = Config::get(ErrorHandlerConstants::API_ERROR_RENDERER, ApiErrorRenderer::class);

            return $this->createApiRenderer($errorRendererClassName);
        }

        if ($this->isCliCall()) {
            return $this->createCliRenderer();
        }

        $errorRendererClassName = Config::get(ErrorHandlerConstants::ERROR_RENDERER, WebHtmlErrorRenderer::class);

        return $this->createWebErrorRenderer($errorRendererClassName);
    }

    /**
     * @return \Spryker\Service\UtilSanitize\UtilSanitizeServiceInterface
     */
    protected function getUtilSanitizeService(): UtilSanitizeServiceInterface
    {
        /** @var \Generated\Service\Ide\AutoCompletion&\Spryker\Shared\Kernel\LocatorLocatorInterface $locator */
        $locator = Locator::getInstance();

        return $locator->utilSanitize()->service();
    }

    /**
     * @return bool
     */
    protected function isGlueApplication()
    {
        return $this->application === static::APPLICATION_GLUE;
    }

    /**
     * @return bool
     */
    protected function isCliCall()
    {
        return (PHP_SAPI === static::SAPI_CLI || PHP_SAPI === static::SAPI_PHPDBG);
    }

    /**
     * @return \Spryker\Shared\ErrorHandler\ErrorRenderer\CliErrorRenderer
     */
    protected function createCliRenderer()
    {
        return new CliErrorRenderer();
    }

    /**
     * @param string $errorRenderer
     *
     * @throws \Exception
     *
     * @return \Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface
     */
    protected function createApiRenderer(string $errorRenderer): ErrorRendererInterface
    {
        if (!class_exists($errorRenderer)) {
            throw new Exception(sprintf('Class %s not found', $errorRenderer));
        }

        $errorRendererObject = new $errorRenderer();

        if (!$errorRendererObject instanceof ErrorRendererInterface) {
            throw new Exception(sprintf('Api error renderer class is expected to be an instance of %s', ErrorRendererInterface::class));
        }

        return $errorRendererObject;
    }

    /**
     * @param string $errorRenderer
     *
     * @return \Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface
     */
    protected function createWebErrorRenderer($errorRenderer)
    {
        /** @phpstan-var class-string<\Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface> $errorRenderer */
        return new $errorRenderer($this->application);
    }
}
