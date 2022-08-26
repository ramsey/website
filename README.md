<h1 align="center">ramsey/website</h1>

<p align="center">
    <strong>My personal website and blog.</strong>
</p>

<p align="center">
    <a href="https://github.com/ramsey/website"><img src="https://img.shields.io/badge/source-ramsey/website-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/php-%5E8.1-8892BF.svg?style=flat-square" alt="PHP Programming Language"></a>
    <a href="https://github.com/ramsey/website/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-MIT%20and%20CC--BY--4.0-darkcyan.svg?style=flat-square" alt="Read License"></a>
    <a href="https://github.com/ramsey/website/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/workflow/status/ramsey/website/build/main?style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/ramsey/website"><img src="https://img.shields.io/codecov/c/gh/ramsey/website?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://shepherd.dev/github/ramsey/website"><img src="https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Framsey%2Fwebsite%2Fcoverage" alt="Psalm Type Coverage"></a>
</p>

## About

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

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.

## Copyright and License

The ramsey/website source code is copyright © [Ben Ramsey](https://benramsey.com)
and licensed for use under the terms of the MIT License (MIT).

Unless otherwise noted, the ramsey/website content is copyright ©
[Ben Ramsey](https://benramsey.com) and licensed for use under the terms of the
Creative Commons Attribution 4.0 International License.

Please see [LICENSE](LICENSE) for more information.
