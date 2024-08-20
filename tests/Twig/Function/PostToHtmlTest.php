<?php

declare(strict_types=1);

namespace App\Tests\Twig\Function;

use App\Entity\Post;
use App\Service\Blog\PostBodyConverter;
use App\Twig\Function\PostToHtml;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Twig\Markup;
use Twig\TwigFunction;

#[TestDox('PostToHtml')]
final class PostToHtmlTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private PostBodyConverter & MockInterface $postBodyConverter;
    private PostToHtml $postToHtml;

    protected function setUp(): void
    {
        $this->postBodyConverter = Mockery::mock(PostBodyConverter::class);
        $this->postToHtml = new PostToHtml($this->postBodyConverter);
    }

    #[TestDox('calls the conversion service when invoked')]
    public function testInvoke(): void
    {
        $post = new Post();

        $this->postBodyConverter->expects('convert')->with($post)->andReturn('converted text');

        $markup = ($this->postToHtml)($post);

        $this->assertInstanceOf(Markup::class, $markup);
        $this->assertSame('converted text', (string) $markup);
    }

    #[TestDox('returns the name of the function')]
    public function testGetFunctionName(): void
    {
        $this->assertSame('post_to_html', $this->postToHtml->getFunctionName());
    }

    #[TestDox('returns the TwigFunction instance of the function')]
    public function testGetTwigFunction(): void
    {
        $function = $this->postToHtml->getTwigFunction();

        $this->assertInstanceOf(TwigFunction::class, $function);
        $this->assertSame('post_to_html', $function->getName());
    }
}
