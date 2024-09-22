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

namespace App\Service\Entity;

use RuntimeException;

/**
 * Thrown when attempting to create an entity that already exists
 *
 * This condition might happen when attempting to use a unique identifier to
 * create or update an entity. If the entity already exists, this exception
 * indicates this state to the caller, which may then choose to perform the same
 * action again by, e.g., passing a parameter that confirms the entity should be
 * updated.
 *
 * For example:
 *
 * ```
 * try {
 *     $entityService->upsertEntity(entity: $entity);
 * } catch (EntityExists) {
 *     if ($io->confirm('Entity already exists. Should we update it?')) {
 *         $entityService->upsertEntity(entity: $entity, doUpdate: true);
 *     }
 * }
 * ```
 */
final class EntityExists extends RuntimeException
{
}
