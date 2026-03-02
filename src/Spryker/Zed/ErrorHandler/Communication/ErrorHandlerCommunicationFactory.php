<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ErrorHandler\Communication;

use Spryker\Zed\ErrorHandler\ErrorHandlerDependencyProvider;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

/**
 * @method \Spryker\Zed\ErrorHandler\ErrorHandlerConfig getConfig()
 */
class ErrorHandlerCommunicationFactory extends AbstractCommunicationFactory
{
    public function getRequestStack(): RequestStack
    {
        return $this->getProvidedDependency(ErrorHandlerDependencyProvider::SERVICE_REQUEST_STACK);
    }

    public function getKernel(): HttpKernelInterface
    {
        return $this->getProvidedDependency(ErrorHandlerDependencyProvider::SERVICE_KERNEL);
    }

    public function getTwig(): Environment
    {
        return $this->getProvidedDependency(ErrorHandlerDependencyProvider::SERVICE_TWIG);
    }

    /**
     * @return array<\Spryker\Zed\ErrorHandlerExtension\Dependency\Plugin\ExceptionHandlerStrategyPluginInterface>
     */
    public function getExceptionHandlerStrategyPlugins(): array
    {
        return $this->getProvidedDependency(ErrorHandlerDependencyProvider::PLUGINS_EXCEPTION_HANDLER);
    }
}
