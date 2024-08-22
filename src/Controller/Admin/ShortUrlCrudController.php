<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ShortUrl;
use App\Service\Entity\ShortUrlService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

use function assert;
use function sprintf;

final class ShortUrlCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ShortUrlService $shortUrlManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ShortUrl::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setEntityLabelInSingular('Short URL')
            ->setEntityLabelInPlural('Short URLs')
            ->setDateFormat('yyyy-MM-dd')
            ->setSearchFields(['slug', 'customSlug', 'destinationUrl'])
            ->setAutofocusSearch()
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();
        yield TextField::new('slug')->onlyOnDetail();
        yield TextField::new('slug', 'Short URL')
            ->setSortable(false)
            ->formatValue(function (string $value, ShortUrl $entity): string {
                $url = $this->shortUrlManager->buildUrl($entity);

                return sprintf('<a href="%s" target="_blank" rel="noopener">%s</a>', $url, $url);
            })
            ->hideOnForm();
        yield UrlField::new('destinationUrl', 'Destination URL');
        yield TextField::new('customSlug')->hideOnIndex();
        yield DateField::new('createdAt')->hideOnForm();
        yield DateField::new('updatedAt')->hideOnForm();
    }

    /**
     * @param class-string<ShortUrl> $entityFqcn
     */
    public function createEntity(string $entityFqcn): ShortUrl
    {
        return (new $entityFqcn())->setSlug($this->shortUrlManager->generateSlug());
    }

    /**
     * @param ShortUrl $entityInstance
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param ShortUrl $entityInstance
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        assert($entityInstance instanceof ShortUrl);

        if ($entityInstance->getCustomSlug() !== null) {
            $this->shortUrlManager->checkAndSetCustomSlug($entityInstance, $entityInstance->getCustomSlug());
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
