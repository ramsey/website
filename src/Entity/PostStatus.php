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

namespace App\Entity;

enum PostStatus: string
{
    /**
     * The post is not accessible to the public. Its URL responds with 404.
     */
    case Deleted = 'deleted';

    /**
     * The post is not accessible to the public. Logged-in users may view the
     * post, while other users see a 404 response.
     */
    case Draft = 'draft';

    /**
     * The post does not appear in the feed or any listings on the website, but
     * users may load access the URL directly.
     */
    case Hidden = 'hidden';

    /**
     * The post is public and appears in the feed and all website listings.
     */
    case Published = 'published';
}
