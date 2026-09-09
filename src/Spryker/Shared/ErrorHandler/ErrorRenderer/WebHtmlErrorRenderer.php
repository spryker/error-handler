<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\ErrorHandler\ErrorRenderer;

use Spryker\Shared\Config\Config;
use Spryker\Shared\ErrorHandler\ErrorHandlerConstants;

class WebHtmlErrorRenderer implements ErrorRendererInterface
{
    /**
     * @var string
     */
    public const APPLICATION_ZED = 'ZED';

    /**
     * Emitted when the configured error page is missing or unreadable, so that a 5xx
     * response never reaches the client with an empty body. Intentionally free of any
     * exception detail, mirroring the generic page shown in production.
     */
    protected const string FALLBACK_ERROR_PAGE_CONTENT = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Internal Server Error</title></head><body><h1>Internal Server Error</h1><p>The server encountered an internal error and could not complete your request.</p></body></html>';

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
     * @param \Exception|\Throwable $exception
     *
     * @return string
     */
    public function render($exception)
    {
        $errorPage = $this->getErrorPageForApplication();

        return $this->getHtmlErrorPageContent($errorPage);
    }

    /**
     * @return string
     */
    protected function getErrorPageForApplication()
    {
        if ($this->application === static::APPLICATION_ZED) {
            return Config::get(ErrorHandlerConstants::ZED_ERROR_PAGE);
        }

        return Config::get(ErrorHandlerConstants::YVES_ERROR_PAGE);
    }

    /**
     * @param string $errorPage
     *
     * @return string
     */
    protected function getHtmlErrorPageContent($errorPage)
    {
        if (!is_file($errorPage) || !is_readable($errorPage)) {
            return static::FALLBACK_ERROR_PAGE_CONTENT;
        }

        return (string)file_get_contents($errorPage);
    }
}
