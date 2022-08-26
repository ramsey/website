<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Metadata;
use App\Service\FinderFactory;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use function sprintf;

/**
 * @psalm-type AuthorYaml = array{name?: string, biography?: string, url?: string, imageUrl?: string, email?: string}
 */
final class AuthorRepository
{
    private const FILENAME_PATTERN = '/^%s\.(yaml|yml)$/';

    public function __construct(
        #[Autowire('%app.content.paths.authorsPath%')] private readonly string $authorsPath,
        private readonly FinderFactory $finderFactory,
        private readonly Parser $yamlParser,
        private readonly UriFactoryInterface $uriFactory,
    ) {
    }

    /**
     * @param array{username?: string} $attributes
     */
    public function findByAttributes(array $attributes): ?Author
    {
        if (isset($attributes['username'])) {
            return $this->findByUsername($attributes['username']);
        }

        return null;
    }

    private function findByUsername(string $authorUsername): ?Author
    {
        $files = $this->finderFactory->createFinder()
            ->files()
            ->in($this->authorsPath)
            ->name(sprintf(self::FILENAME_PATTERN, $authorUsername));

        if ($files->count() > 1) {
            throw new MultipleMatchesException(sprintf(
                'More than one author matches "%s"',
                $authorUsername,
            ));
        }

        foreach ($files as $file) {
            return $this->convertToAuthor($file);
        }

        return null;
    }

    private function convertToAuthor(SplFileInfo $file): Author
    {
        /** @var AuthorYaml $yaml */
        $yaml = $this->yamlParser->parse($file->getContents()) ?: [];

        $name = $yaml['name'] ?? $file->getFilenameWithoutExtension();
        $biography = $yaml['biography'] ?? null;
        $url = $yaml['url'] ?? null;
        $imageUrl = $yaml['imageUrl'] ?? null;
        $email = $yaml['email'] ?? null;

        unset($yaml['name'], $yaml['biography'], $yaml['url'], $yaml['imageUrl'], $yaml['email']);

        $uriInstance = $url ? $this->uriFactory->createUri($url) : null;
        $imageUriInstance = $imageUrl ? $this->uriFactory->createUri($imageUrl) : null;
        $metadata = new Metadata($yaml);

        return new Author(
            name: $name,
            biography: $biography,
            url: $uriInstance,
            imageUrl: $imageUriInstance,
            email: $email,
            metadata: $metadata,
        );
    }
}
