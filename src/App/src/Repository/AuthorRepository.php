<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Attributes;
use App\Entity\Author;
use App\Repository\Exception\MultipleMatches;
use App\Util\FinderFactory;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use function sprintf;

class AuthorRepository
{
    private const FILENAME_PATTERN = '/^%s\.(yaml|yml)$/';

    public function __construct(
        private FinderFactory $finderFactory,
        private string $authorsPath,
        private Parser $yamlParser,
        private UriFactoryInterface $uriFactory,
    ) {
    }

    public function find(string $authorUsername): ?Author
    {
        $files = ($this->finderFactory)()
            ->files()
            ->in($this->authorsPath)
            ->name(sprintf(self::FILENAME_PATTERN, $authorUsername));

        if ($files->count() > 1) {
            throw new MultipleMatches(sprintf(
                'More than one author matches %s',
                $authorUsername,
            ));
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            return $this->convertToAuthor($file);
        }

        return null;
    }

    private function convertToAuthor(SplFileInfo $file): Author
    {
        /** @var array<string, mixed> $yaml */
        $yaml = $this->yamlParser->parse($file->getContents());

        /** @var string $name */
        $name = $yaml['name'] ?? $file->getFilenameWithoutExtension();

        /** @var string|null $biography */
        $biography = $yaml['biography'] ?? null;

        /** @var string|null $url */
        $url = $yaml['url'] ?? null;

        /** @var string|null $imageUrl */
        $imageUrl = $yaml['imageUrl'] ?? null;

        /** @var string|null $email */
        $email = $yaml['email'] ?? null;

        unset($yaml['name'], $yaml['biography'], $yaml['url'], $yaml['imageUrl'], $yaml['email']);

        $uriInstance = $url ? $this->uriFactory->createUri($url) : null;
        $imageUriInstance = $imageUrl ? $this->uriFactory->createUri($imageUrl) : null;
        $attributes = new Attributes($yaml);

        return new Author(
            name: $name,
            biography: $biography,
            url: $uriInstance,
            imageUrl: $imageUriInstance,
            email: $email,
            attributes: $attributes,
        );
    }
}
