<h1 align="center">ramsey/website</h1>

<p align="center">
    <strong>My personal website and blog.</strong>
</p>

<p align="center">
    <a href="https://github.com/ramsey/website"><img src="https://img.shields.io/badge/source-ramsey/website-blue.svg?style=flat-square" alt="Source Code"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/php-%5E8.3-color.svg?style=flat-square&colorB=%238892BF" alt="PHP Programming Language"></a>
    <a href="https://github.com/ramsey/website/blob/main/COPYING"><img src="https://img.shields.io/badge/source-AGPL--3.0-darkcyan.svg?style=flat-square" alt="License for source code"></a>
    <a href="https://github.com/ramsey/website/blob/main/COPYING.content"><img src="https://img.shields.io/badge/content-CC--BY--SA--4.0-darkcyan.svg?style=flat-square" alt="License for content"></a>
    <a href="https://github.com/ramsey/website/blob/main/COPYING.examples"><img src="https://img.shields.io/badge/examples-CC0--1.0-darkcyan.svg?style=flat-square" alt="License for code examples in content"></a>
    <a href="https://github.com/ramsey/website/actions/workflows/continuous-integration.yml"><img src="https://img.shields.io/github/actions/workflow/status/ramsey/website/continuous-integration.yml?branch=main&style=flat-square&logo=github" alt="Build Status"></a>
    <a href="https://codecov.io/gh/ramsey/website"><img src="https://img.shields.io/codecov/c/gh/ramsey/website?label=codecov&logo=codecov&style=flat-square" alt="Codecov Code Coverage"></a>
    <a href="https://status.ben.ramsey.dev/?utm_source=status_badge"><img src="https://uptime.betterstack.com/status-badges/v1/monitor/1g04j.svg" alt="Better Stack uptime"></a>
</p>

## About

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md). By
participating in this project and its community, you are expected to uphold this
code.

## Getting Started

Clone this repository, then `cd` into the `website` directory, install the
dependencies, and run the local development web server:

```bash
git clone https://github.com/ramsey/website website
cd website
composer install

# Be sure the Symfony CLI is installed: https://symfony.com/download
symfony server:start
```

You can then browse to <http://localhost:8000>.

## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Coordinated Disclosure

Keeping user information safe and secure is a top priority, and we welcome the
contribution of external security researchers. If you believe you've found a
security issue in software that is maintained in this repository, please read
[SECURITY.md](SECURITY.md) for instructions on submitting a vulnerability report.

## Copyright and License

### Website Content

Except where otherwise noted, all content, excluding code examples, on this website
is Copyright © [Ben Ramsey](https://ben.ramsey.dev) and is licensed under a
[Creative Commons Attribution-ShareAlike 4.0 International license](https://creativecommons.org/licenses/by-sa/4.0/).
Please see [COPYING.content](COPYING.content) for more information.

### Code Examples in Website Content

Code examples within the content of this website, unless indicated otherwise,
are marked with [CC0 1.0 Universal (CC0-1.0)](https://creativecommons.org/publicdomain/zero/1.0/)
and dedicated to the public domain. To the extent possible under law, Ben Ramsey
has waived all copyright and related or neighboring rights to the code examples
appearing within the content of this website. Please see
[COPYING.examples](COPYING.examples) for more information.

### Source Code

Except where otherwise noted, all source code for this website is Copyright ©
[Ben Ramsey](https://ben.ramsey.dev) and contributors and is licensed for use
under the terms of the GNU Affero General Public License (AGPL-3.0-or-later) as
published by the Free Software Foundation. Please see [COPYING](COPYING) and
[NOTICE](NOTICE) for more information.
