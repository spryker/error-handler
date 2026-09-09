<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\ErrorHandler;

use Codeception\Test\Unit;
use Exception;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Spryker\Service\UtilSanitize\UtilSanitizeService;
use Spryker\Shared\ErrorHandler\ErrorHandler;
use Spryker\Shared\ErrorHandler\ErrorLogger;
use Spryker\Shared\ErrorHandler\ErrorLoggerInterface;
use Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group ErrorHandler
 * @group ErrorHandlerTest
 * Add your own group annotations below this line
 */
class ErrorHandlerTest extends Unit
{
    /**
     * @var \SprykerTest\Shared\ErrorHandler\ErrorHandlerTester
     */
    protected $tester;

    public function testIfHandleExceptionThrowsExceptionErrorLoggerShouldLogBeforeExceptionAndLogExceptionAndSendExitCode(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->exactly(2))->method('log');

        $exception = new Exception('Test exception');

        $errorRendererMock = $this->getErrorRendererMock();

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);
        $errorHandlerMock->expects($this->once())->method('send500Header')->willThrowException($exception);

        $errorHandlerMock->expects($this->never())->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->once())->method('sendExitCode');

        $this->expectOutputString($this->getFallbackErrorBody());

        $errorHandlerMock->handleException($exception);
    }

    public function testZedErrorPageSends404StatusForNotFoundHttpException(): void
    {
        // Arrange
        $exception = new NotFoundHttpException();

        $errorHandlerMock = $this->getErrorHandlerMock($this->getErrorLoggerMock(), $this->getErrorRendererMock());
        // Assert
        $errorHandlerMock->expects($this->once())->method('send404Header')->willThrowException($exception);
        $this->expectOutputString($this->getFallbackErrorBody());

        // Act
        $errorHandlerMock->handleException($exception);
    }

    public function testHandleExceptionSanitizesExceptionMessageBeforeRendering(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->exactly(1))->method('log');
        $exception = new Exception("Test exception: <script>alert('XSS');</script>");

        $errorRendererMock = $this->getMockBuilder(ErrorRendererInterface::class)
            ->getMock();
        $errorRendererMock->expects($this->once())
            ->method('render')
            ->with(
                new Exception('Test exception: &lt;script&gt;alert(&#039;XSS&#039;);&lt;/script&gt;'),
            );

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);
        $errorHandlerMock->expects($this->once())
            ->method('send500Header')
            ->willReturn(null);

        $errorHandlerMock->expects($this->once())
            ->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->once())
            ->method('sendExitCode');

        // Act
        $errorHandlerMock->handleException($exception);
    }

    public function testIfHandleExceptionThrowsExceptionErrorLoggerShouldLogBeforeExceptionAndLogExceptionAndShouldNotSendExitCode(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->exactly(2))->method('log');

        $exception = new Exception('Test exception');

        $errorRendererMock = $this->getErrorRendererMock();

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);
        $errorHandlerMock->expects($this->once())->method('send500Header')->willThrowException($exception);

        $errorHandlerMock->expects($this->never())->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->never())->method('sendExitCode');

        $this->expectOutputString($this->getFallbackErrorBody());

        $errorHandlerMock->handleException($exception, false);
    }

    public function testHandleExceptionShouldLogRenderErrorAndSendExitCode(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->once())->method('log');

        $errorRendererMock = $this->getErrorRendererMock();
        $errorRendererMock->expects($this->once())->method('render');

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);

        $errorHandlerMock->expects($this->once())->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->once())->method('sendExitCode');

        $errorHandlerMock->handleException(new Exception());
    }

    public function testHandleExceptionShouldLogRenderErrorAndNotSendExitCode(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->once())->method('log');

        $errorRendererMock = $this->getErrorRendererMock();
        $errorRendererMock->expects($this->once())->method('render');

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);

        $errorHandlerMock->expects($this->once())->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->never())->method('sendExitCode');

        $errorHandlerMock->handleException(new Exception(), false);
    }

    public function testHandleExceptionEmitsFallbackBodyWhenRendererThrows(): void
    {
        // Arrange
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->exactly(2))->method('log');

        $errorRendererMock = $this->getErrorRendererMock();
        $errorRendererMock->expects($this->once())
            ->method('render')
            ->willThrowException(new Exception('Renderer failure'));

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);
        $errorHandlerMock->expects($this->once())->method('send500Header');
        $errorHandlerMock->expects($this->once())->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->never())->method('sendExitCode');

        $fallbackErrorBody = $this->getFallbackErrorBody();

        // Assert
        $this->assertNotSame('', $fallbackErrorBody, 'A failing renderer must never result in an empty response body.');
        $this->assertStringNotContainsString('Renderer failure', $fallbackErrorBody);
        $this->assertStringNotContainsString('Original exception', $fallbackErrorBody);
        $this->assertStringNotContainsString('<', $fallbackErrorBody, 'The renderer replaced here may be API, CLI or Web, so the fallback must carry no markup.');
        $this->expectOutputString($fallbackErrorBody);

        // Act
        $errorHandlerMock->handleException(new Exception('Original exception'), false);
    }

    public function testHandleExceptionEmitsRenderedBodyAndNoFallbackWhenRendererSucceeds(): void
    {
        // Arrange
        $renderedBody = '<html lang="en"><body>Configured error page</body></html>';

        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorLoggerMock->expects($this->once())->method('log');

        $errorRendererMock = $this->getErrorRendererMock();
        $errorRendererMock->expects($this->once())->method('render')->willReturn($renderedBody);

        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock);
        $errorHandlerMock->expects($this->once())->method('cleanOutputBuffer');
        $errorHandlerMock->expects($this->never())->method('sendExitCode');

        // Assert
        $this->expectOutputString($renderedBody);

        // Act
        $errorHandlerMock->handleException(new Exception('Original exception'), false);
    }

    public function testHandleFatalShouldCallHandleExceptionWhenLastErrorExists(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorRendererMock = $this->getErrorRendererMock();
        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock, ['getLastError', 'handleException']);
        $errorHandlerMock->expects($this->once())->method('handleException');
        $errorHandlerMock->expects($this->once())->method('getLastError')->willReturn(
            ['message' => 'message', 'file' => 'file', 'type' => 1, 'line' => 123],
        );

        $errorHandlerMock->handleFatal();
    }

    public function testHandleFatalShouldNotCallHandleExceptionWhenNoLastErrorExists(): void
    {
        $errorLoggerMock = $this->getErrorLoggerMock();
        $errorRendererMock = $this->getErrorRendererMock();
        $errorHandlerMock = $this->getErrorHandlerMock($errorLoggerMock, $errorRendererMock, ['getLastError', 'handleException']);
        $errorHandlerMock->expects($this->never())->method('handleException');
        $errorHandlerMock->expects($this->once())->method('getLastError')->willReturn(null);

        $errorHandlerMock->handleFatal();
    }

    /**
     * @param \Spryker\Shared\ErrorHandler\ErrorLoggerInterface $errorLogger
     * @param \Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface $errorRenderer
     * @param array $methods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Shared\ErrorHandler\ErrorHandler
     */
    protected function getErrorHandlerMock(
        ErrorLoggerInterface $errorLogger,
        ErrorRendererInterface $errorRenderer,
        array $methods = []
    ): ErrorHandler {
        $mockMethods = [
            'cleanOutputBuffer',
            'sendExitCode',
            'send500Header',
            'send404Header',
        ];

        $methods = array_merge($mockMethods, $methods);

        $errorHandlerMock = $this->getMockBuilder(ErrorHandler::class)
            ->onlyMethods($methods)
            ->setConstructorArgs([$errorLogger, $errorRenderer, new UtilSanitizeService()])
            ->getMock();

        return $errorHandlerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Shared\ErrorHandler\ErrorLogger
     */
    protected function getErrorLoggerMock(): ErrorLogger
    {
        $loggerMock = $this->getLoggerMock();

        $errorLoggerMock = $this->getMockBuilder(ErrorLogger::class)
            ->onlyMethods(['getLogger', 'log'])
            ->getMock();

        $errorLoggerMock->method('getLogger')->willReturn($loggerMock);

        return $errorLoggerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Psr\Log\LoggerInterface
     */
    protected function getLoggerMock(): LoggerInterface
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        return $loggerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface
     */
    protected function getErrorRendererMock(): ErrorRendererInterface
    {
        $errorRendererMock = $this->getMockBuilder(ErrorRendererInterface::class)
            ->getMock();

        return $errorRendererMock;
    }

    protected function getFallbackErrorBody(): string
    {
        $reflection = new ReflectionClass(ErrorHandler::class);

        return (string)$reflection->getConstant('FALLBACK_ERROR_BODY');
    }
}
