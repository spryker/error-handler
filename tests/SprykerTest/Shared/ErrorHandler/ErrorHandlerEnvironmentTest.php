<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\ErrorHandler;

use Codeception\Test\Unit;
use Spryker\Shared\ErrorHandler\ErrorHandlerEnvironment;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group ErrorHandler
 * @group ErrorHandlerEnvironmentTest
 * Add your own group annotations below this line
 */
class ErrorHandlerEnvironmentTest extends Unit
{
    public function testInitializeShouldSetErrorHandler(): void
    {
        $errorHandlerEnvironment = new ErrorHandlerEnvironment();
        $errorHandlerEnvironment->initialize();

        $this->resetHandlersToDefault();
    }

    protected function resetHandlersToDefault(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }
}
