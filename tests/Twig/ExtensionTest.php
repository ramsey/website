<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\Extension;
use App\Twig\Function\TwigFunctionFactory;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

#[TestDox('App\\Twig\\Extension')]
class ExtensionTest extends TestCase
{
    #[TestDox('registers a function factory with the extension')]
    public function testAddFunctionFactoryAddsFunction(): void
    {
        $twigFunction = new TwigFunction('foo', fn () => 'foo');
        $functionFactory = new readonly class ($twigFunction) implements TwigFunctionFactory {
            public function __construct(private TwigFunction $twigFunction)
            {
            }

            public function getFunctionName(): string
            {
                return 'foo';
            }

            public function getTwigFunction(): TwigFunction
            {
                return $this->twigFunction;
            }
        };

        $extension = new Extension();
        $extension->addFunctionFactory($functionFactory);
        $functions = $extension->getFunctions();

        $this->assertContains($twigFunction, $functions);
    }
}
