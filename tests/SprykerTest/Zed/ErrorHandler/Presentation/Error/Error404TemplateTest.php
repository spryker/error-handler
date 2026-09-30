<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Zed\ErrorHandler\Presentation\Error;

use Codeception\Test\Unit;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ErrorHandler
 * @group Presentation
 * @group Error
 * @group Error404TemplateTest
 * Add your own group annotations below this line
 */
class Error404TemplateTest extends Unit
{
    protected const string TEMPLATE_NAME = '@ErrorHandler/Error/error404.twig';

    protected const string TWIG_NAMESPACE = 'ErrorHandler';

    protected const string PRESENTATION_DIRECTORY = '/../../../../../../src/Spryker/Zed/ErrorHandler/Presentation';

    /**
     * The Merchant Portal router has no route named "home", so the template must render without router functions.
     */
    public function testRenderLinksHomeToRootPathWithoutRouterFunctions(): void
    {
        // Arrange
        $twig = $this->createTwigEnvironment();

        // Act
        $content = $twig->render(static::TEMPLATE_NAME, [
            'app' => ['locale' => 'en_US'],
            'error' => 'No route found',
            'errorCode' => 404,
        ]);

        // Assert
        $this->assertStringContainsString('href="/"', $content);
    }

    protected function createTwigEnvironment(): Environment
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__ . static::PRESENTATION_DIRECTORY, static::TWIG_NAMESPACE);

        $twig = new Environment($loader);
        $twig->addFilter(new TwigFilter('trans', fn (string $message): string => $message));

        return $twig;
    }
}
