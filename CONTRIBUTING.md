# Contributing

Contributions are welcome. This project accepts pull requests on [GitHub][].

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md). By
participating in this project and its community, you are expected to uphold this
code.

## Communication Channels

You can find help and discussion in the following places:

* GitHub Issues: <https://github.com/ramsey/website/issues>

## Reporting Bugs

Report bugs using the project's [issue tracker][issues].

> [!IMPORTANT]
> DO NOT include passwords or other sensitive information in your bug report.

When submitting a bug report, please include enough information to reproduce the
bug. A good bug report includes the following sections:

* **Description**

  Provide a short and clear description of the bug.

* **Steps to reproduce**

  Provide steps to reproduce the behavior you are experiencing. Please try to
  keep this as short as possible. If able, create a reproducible script outside
  of any framework you are using. This will help us to quickly debug the issue.

* **Expected behavior**

  Provide a short and clear description of what you expect to happen.

* **Screenshots or output**

  If applicable, add screenshots or program output to help explain your problem.

* **Environment details**

  Provide details about the system where you're using this package, such as PHP
  version and operating system.

* **Additional context**

  Provide any additional context that may help us debug the problem.

## Fixing Bugs

This project welcomes pull requests to fix bugs!

If you see a bug report that you'd like to fix, please feel free to do so.
Following the directions and guidelines described in the "Adding New Features"
section below, you may create bugfix branches and send pull requests.

## Adding New Features

If you have an idea for a new feature, it's a good idea to check out the
[issues][] or active [pull requests][] first to see if anyone is already working
on the feature. If not, feel free to submit an issue first, asking whether the
feature is beneficial to the project. This will save you from doing a lot of
development work only to have your feature rejected. We don't enjoy rejecting
your hard work, but some features don't fit with the goals of the project.

When you do begin working on your feature, here are some guidelines to consider:

* Your pull request description should clearly detail the changes you have made.
  We will use this description to update the CHANGELOG. If there is no
  description, or it does not adequately describe your feature, we may ask you
  to update the description.
* ramsey/website follows a superset of **[PSR-12 coding standard][psr-12]**.
  Please ensure your code does, too. _Hint: run `composer lint` to check._
* Please **write tests** for any new features you add.
* Please **ensure that tests pass** before submitting your pull request.
  ramsey/website automatically runs tests for pull requests. However,
  running the tests locally will help save time. _Hint: run `composer test`._
* **Use topic/feature branches.** Please do not ask to pull from your main branch.
  * For more information, see "[Understanding the GitHub flow][gh-flow]."
* **Submit one feature per pull request.** If you have multiple features you
  wish to submit, please break them into separate pull requests.
* **Write good commit messages.** This project follows the
  [Conventional Commits][] specification and uses Git hooks to ensure all
  commits follow this standard. Running `composer install` will set up the Git
  hooks, so when you run `git commit`, you'll be prompted to create a commit
  using the Conventional Commits rules.

## Developing

To develop this project, you will need [PHP](https://www.php.net) 8.3 or greater
and [Composer](https://getcomposer.org).

After cloning this repository locally, execute the following commands:

``` bash
cd /path/to/repository
composer install
```

Now, you are ready to develop!

### Tooling

#### Symfony

This project uses the [Symfony](https://symfony.com) framework. For more
information about Symfony and the tools it provides, check out the
[Symfony Documentation](https://symfony.com/doc).

Use the [Symfony CLI](https://symfony.com/doc/current/setup/symfony_server.html)
to run the local web server.

``` bash
symfony server:start
```

Then, use `symfony open:local` to open a web browser and load the website for
local development.

#### CaptainHook

This project uses [CaptainHook](https://github.com/CaptainHookPhp/captainhook)
to validate all staged changes prior to commit.

#### Commands

There are some custom commands available for developing and contributing to this
project. To see all the commands:

``` bash
composer list
```

Additionally, Symfony provides many helpful commands:

``` bash
./bin/console
```

### Coding Standards

This project follows a superset of [PSR-12](https://www.php-fig.org/psr/psr-12/)
coding standards, enforced by [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

CaptainHook will run coding standards checks before committing.

You may lint the codebase manually using the following commands:

``` bash
# Lint
composer lint

# Attempt to auto-fix coding standards issues
composer lint:fix
```

### Static Analysis

This project uses a [PHPStan](https://github.com/phpstan/phpstan) to provide
static analysis of PHP code.

CaptainHook will run static analysis checks before committing.

You may run static analysis manually across the whole codebase with the
following command:

``` bash
# Static analysis
composer analyze
```

### Running Tests

The following must pass before we will accept a pull request. If this does not
pass, it will result in a complete build failure. Before you can run this, be
sure to `composer install`.

To run all the tests and coding standards checks, execute the following from the
command line, while in the project root directory:

``` bash
composer test
```

CaptainHook will automatically run all tests before pushing to the remote
repository.

[github]: https://github.com/ramsey/website
[issues]: https://github.com/ramsey/website/issues
[pull requests]: https://github.com/ramsey/website/pulls
[psr-12]: https://www.php-fig.org/psr/psr-12/
[gh-flow]: https://guides.github.com/introduction/flow/
[conventional commits]: https://www.conventionalcommits.org/
