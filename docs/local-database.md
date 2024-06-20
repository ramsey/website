# Local Database Development

You can use the local database created with the provided
[Docker Compose](https://docs.docker.com/compose/) file. It's a PostgreSQL
database, and the configuration settings should be fine, without changing
anything, since they're used only in local development.

Start up the database:

```shell
docker compose up -d
```

The primary database will be used for local development. You can create it
(if needed) and run the migrations to get the schema set up (or updated since
the last time you ran migrations) with:

```shell
./bin/console doctrine:database:create
./bin/console doctrine:migrations:migrate
```

## Test Database

To run tests, you'll need to set up a database for the `test` environment.
Use the same database as before (i.e., `docker compose up -d`), run the
following commands, which will execute them for the test environment, and
Symfony/Doctrine will automatically add the suffix `_test` to the database name
that's created.

```shell
composer test:db:setup
composer test:db:fixtures
```

Now, you can run the tests (i.e., `composer test`), and the test database will
be ready for the integration tests.

If you need to drop the test database to recreate it:

```shell
composer test:db:teardown
```
