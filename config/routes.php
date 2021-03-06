<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * FastRoute route configuration
 *
 * @see https://github.com/nikic/FastRoute
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/{id:\d+}', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/', App\Handler\HomeHandler::class, 'home');
    $app->get('/blog/feed.xml', App\Handler\Blog\FeedHandler::class, 'blog.feed');
    $app->get('/blog[/{year:\d{4}}[/{month:\d{2}}]]', App\Handler\Blog\ListHandler::class, 'blog.list');
    $app->get('/blog/{year:\d{4}}/{month:\d{2}}/{slug}', App\Handler\Blog\PostHandler::class, 'blog.post.old');
    $app->get('/blog/{year:\d{4}}/{slug}', App\Handler\Blog\PostHandler::class, 'blog.post');
    $app->get('/{slug:.+}', App\Handler\PageHandler::class, 'page');
};
