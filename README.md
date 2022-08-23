<h1 align="center">ramsey/website</h1>

<p align="center">
    <strong>My personal website and blog.</strong>
</p>

<!--
TODO: Make sure the following URLs are correct and working for your project.
      Then, remove these comments to display the badges, giving users a quick
      overview of your package.

<p align="center">
    <a href="https://github.com/ramsey/website-base"><img src="https://img.shields.io/badge/source-ramsey/website-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://packagist.org/packages/ramsey/website"><img src="https://img.shields.io/packagist/v/ramsey/website.svg?style=flat-square&label=release" alt="Download Package"></a>
    <a href="https://php.net"><img src="https://img.shields.io/packagist/php-v/ramsey/website.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/ramsey/website-base/blob/main/LICENSE"><img src="https://img.shields.io/packagist/l/ramsey/website.svg?style=flat-square&colorB=darkcyan" alt="Read License"></a>
    <a href="https://github.com/ramsey/website-base/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/workflow/status/ramsey/website-base/build/main?style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/ramsey/website-base"><img src="https://img.shields.io/codecov/c/gh/ramsey/website-base?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://shepherd.dev/github/ramsey/website-base"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Framsey%2Fwebsite-base%2Fcoverage" alt="Psalm Type Coverage"></a>
</p>
-->


## About

<!--
TODO: Use this space to provide more details about your package. Try to be
      concise. This is the introduction to your package. Let others know what
      your package does and how it can help them build applications.
-->


This project adheres to a [code of conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to
uphold this code.


## Development

To run this project locally for development:

``` bash
git clone https://github.com/ramsey/website.git
cd website/
composer install && yarn install
composer dev:serve:watch
```

Then, go to <https://127.0.0.1:9000>.

To stop the server, run `symfony server:stop`. To view the server logs while
the server is running, run `symfony server:log`.

<!--
## Usage

Provide a brief description or short example of how to use this library.
If you need to provide more detailed examples, use the `docs/` directory
and provide a link here to the documentation.

``` php
use Ramsey\Website\Example;

$example = new Example();
echo $example->greet('fellow human');
```
-->


## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.






## Copyright and License

The ramsey/website library is copyright © [Ben Ramsey](https://benramsey.com)
and licensed for use under the terms of the
MIT License (MIT). Please see [LICENSE](LICENSE) for more information.


