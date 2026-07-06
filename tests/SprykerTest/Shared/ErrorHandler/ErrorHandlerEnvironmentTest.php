<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\ErrorHandler;

use Codeception\Test\Unit;
use ErrorException;
use Spryker\Shared\ErrorHandler\ErrorHandlerConstants;
use Spryker\Shared\ErrorHandler\ErrorHandlerEnvironment;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group ErrorHandler
 * @group ErrorHandlerEnvironmentTest
 * Add your own group annotations below this line
 *
 * @property \SprykerTest\Shared\ErrorHandler\ErrorHandlerSharedTester $tester
 */
class ErrorHandlerEnvironmentTest extends Unit
{
    protected int $originalErrorReporting = 0;

    protected function _before(): void
    {
        $this->originalErrorReporting = error_reporting();
    }

    protected function _after(): void
    {
        restore_error_handler();
        error_reporting($this->originalErrorReporting);
    }

    public function testInitializeShouldSetErrorHandler(): void
    {
        $errorHandlerEnvironment = new ErrorHandlerEnvironment();
        $errorHandlerEnvironment->initialize();

        restore_error_handler();
        restore_exception_handler();
    }

    public function testSuppressedWarningDoesNotThrow(): void
    {
        $this->installErrorHandlerReporting(E_ALL);
        error_reporting(E_ALL & ~E_USER_WARNING);

        $isThrown = false;

        try {
            trigger_error('suppressed warning', E_USER_WARNING);
        } catch (ErrorException) {
            $isThrown = true;
        }

        $this->assertFalse($isThrown, 'A `@`-suppressed warning must not be escalated into an ErrorException.');
    }

    public function testUnsuppressedWarningThrows(): void
    {
        $this->installErrorHandlerReporting(E_ALL);

        $this->expectException(ErrorException::class);

        trigger_error('real warning', E_USER_WARNING);
    }

    public function testLogOnlyLevelDoesNotThrow(): void
    {
        $this->tester->setConfig(ErrorHandlerConstants::ERROR_LEVEL_LOG_ONLY, E_USER_DEPRECATED);
        $this->installErrorHandlerReporting(E_ALL);

        $isThrown = false;

        try {
            trigger_error('deprecated', E_USER_DEPRECATED);
        } catch (ErrorException) {
            $isThrown = true;
        }

        $this->assertFalse($isThrown, 'A log-only level must be logged instead of thrown.');
    }

    protected function installErrorHandlerReporting(int $errorLevel): void
    {
        $this->tester->setConfig(ErrorHandlerConstants::ERROR_LEVEL, $errorLevel);

        (new class extends ErrorHandlerEnvironment {
            public function install(): void
            {
                $this->setErrorHandler();
            }
        })->install();
    }
}
