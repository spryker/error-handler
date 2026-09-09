<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\ErrorHandler\ErrorRenderer;

use Codeception\Test\Unit;
use Exception;
use ReflectionClass;
use Spryker\Shared\Config\Config;
use Spryker\Shared\ErrorHandler\ErrorHandlerConstants;
use Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Spryker\Shared\ErrorHandler\ErrorRenderer\WebHtmlErrorRenderer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Shared
 * @group ErrorHandler
 * @group ErrorRenderer
 * @group WebErrorHtmlRendererTest
 * Add your own group annotations below this line
 */
class WebErrorHtmlRendererTest extends Unit
{
    /**
     * @var string|null
     */
    protected $temporaryErrorPagePath;

    protected function tearDown(): void
    {
        if ($this->temporaryErrorPagePath !== null && is_file($this->temporaryErrorPagePath)) {
            unlink($this->temporaryErrorPagePath);
        }

        $this->temporaryErrorPagePath = null;

        parent::tearDown();
    }

    public function testWhenZedErrorPageCanRequiredRequireErrorPage(): void
    {
        $this->setupConfigForZedErrorPage();

        $errorPageMock = $this->getErrorPageMock('ZED');
        $errorPageMock->method('getHtmlErrorPageContent')->with(ErrorHandlerConstants::ZED_ERROR_PAGE);

        $errorPageMock->render(new Exception());
    }

    protected function setupConfigForZedErrorPage(): void
    {
        $configKey = ErrorHandlerConstants::ZED_ERROR_PAGE;
        $configValue = ErrorHandlerConstants::ZED_ERROR_PAGE;

        $this->prepareConfig($configKey, $configValue);
    }

    public function testWhenYvesErrorPageCanRequiredRequireErrorPage(): void
    {
        $this->setupConfigForYvesErrorPage();

        $errorPageMock = $this->getErrorPageMock('YVES');
        $errorPageMock->method('getHtmlErrorPageContent')->with(ErrorHandlerConstants::YVES_ERROR_PAGE);

        $errorPageMock->render(new Exception());
    }

    protected function setupConfigForYvesErrorPage(): void
    {
        $configKey = ErrorHandlerConstants::YVES_ERROR_PAGE;
        $configValue = ErrorHandlerConstants::YVES_ERROR_PAGE;

        $this->prepareConfig($configKey, $configValue);
    }

    protected function prepareConfig(string $configKey, string $configValue): void
    {
        $reflection = new ReflectionClass(Config::class);
        $reflectionProperty = $reflection->getProperty('config');

        $config = $reflectionProperty->getValue();
        $config[$configKey] = $configValue;

        $reflectionProperty->setValue($config);
    }

    /**
     * @param string $application
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface
     */
    protected function getErrorPageMock(string $application): ErrorRendererInterface
    {
        $errorPageMock = $this->getMockBuilder(WebHtmlErrorRenderer::class)
            ->onlyMethods(['getHtmlErrorPageContent'])
            ->setConstructorArgs([$application])
            ->getMock();

        return $errorPageMock;
    }

    public function testRenderReturnsConfiguredErrorPageContentUnchangedWhenErrorPageExists(): void
    {
        // Arrange
        $errorPageContent = '<html lang="en"><body>Configured error page</body></html>';
        $errorPagePath = $this->createTemporaryErrorPage($errorPageContent);
        $this->prepareConfig(ErrorHandlerConstants::ZED_ERROR_PAGE, $errorPagePath);

        $errorRenderer = new WebHtmlErrorRenderer(WebHtmlErrorRenderer::APPLICATION_ZED);

        // Act
        $renderedContent = $errorRenderer->render(new Exception('Test exception'));

        // Assert
        $this->assertSame($errorPageContent, $renderedContent);
    }

    public function testRenderReturnsFallbackContentWhenConfiguredErrorPageDoesNotExist(): void
    {
        // Arrange
        $errorPagePath = $this->getNonExistingErrorPagePath();
        $this->prepareConfig(ErrorHandlerConstants::ZED_ERROR_PAGE, $errorPagePath);

        $errorRenderer = new WebHtmlErrorRenderer(WebHtmlErrorRenderer::APPLICATION_ZED);

        // Act
        $renderedContent = $errorRenderer->render(new Exception('Test exception'));

        // Assert
        $this->assertNotSame('', $renderedContent, 'The renderer must never return an empty body for a missing error page.');
        $this->assertSame($this->getFallbackErrorPageContent(), $renderedContent);
        $this->assertStringNotContainsString($errorPagePath, $renderedContent);
        $this->assertStringNotContainsString('Test exception', $renderedContent);
    }

    public function testRenderReturnsFallbackContentWhenConfiguredErrorPageIsNotAFile(): void
    {
        // Arrange
        $this->prepareConfig(ErrorHandlerConstants::YVES_ERROR_PAGE, sys_get_temp_dir());

        $errorRenderer = new WebHtmlErrorRenderer('YVES');

        // Act
        $renderedContent = $errorRenderer->render(new Exception('Test exception'));

        // Assert
        $this->assertNotSame('', $renderedContent, 'The renderer must never return an empty body for an unreadable error page.');
        $this->assertSame($this->getFallbackErrorPageContent(), $renderedContent);
    }

    protected function createTemporaryErrorPage(string $errorPageContent): string
    {
        $errorPagePath = (string)tempnam(sys_get_temp_dir(), 'spryker-error-page-');
        file_put_contents($errorPagePath, $errorPageContent);
        $this->temporaryErrorPagePath = $errorPagePath;

        return $errorPagePath;
    }

    protected function getNonExistingErrorPagePath(): string
    {
        $errorPagePath = sys_get_temp_dir() . '/spryker-missing-error-page-' . uniqid() . '/5xx.html';
        $this->assertFileDoesNotExist($errorPagePath);

        return $errorPagePath;
    }

    protected function getFallbackErrorPageContent(): string
    {
        $reflection = new ReflectionClass(WebHtmlErrorRenderer::class);

        return (string)$reflection->getConstant('FALLBACK_ERROR_PAGE_CONTENT');
    }
}
