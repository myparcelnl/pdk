# MyParcel Plugin Development Kit

[![Latest version](https://img.shields.io/github/v/release/myparcelnl/pdk)](https://github.com/myparcelnl/pdk/releases/latest)
[![Packagist Downloads](https://img.shields.io/packagist/dm/myparcelnl/pdk)](https://packagist.org/packages/myparcelnl/pdk)
[![Codacy grade](https://img.shields.io/codacy/grade/c0f99d91962d448287b438b8162fff51)](https://app.codacy.com/gh/myparcelnl/pdk/dashboard)
[![Code coverage](https://img.shields.io/codecov/c/github/myparcelnl/pdk)](https://codecov.io/gh/myparcelnl/pdk)
![PHPStan](https://img.shields.io/badge/dynamic/yaml?url=https%3A%2F%2Fraw.githubusercontent.com%2Fmyparcelnl%2Fpdk%2Fmain%2Fphpstan.neon.dist&query=%24.parameters.level&label=PHPStan%20level&color=brightgreen)
![License](https://img.shields.io/github/license/myparcelnl/pdk)
[![Chat with us](https://img.shields.io/badge/Slack-Chat%20with%20us-white?logo=slack&labelColor=4a154b)](https://join.slack.com/t/myparcel-dev/shared_invite/enQtNDkyNTg3NzA1MjM4LTM0Y2IzNmZlY2NkOWFlNTIyODY5YjFmNGQyYzZjYmQzMzliNDBjYzBkOGMwYzA0ZDYzNmM1NzAzNDY1ZjEzOTM)

The MyParcel PDK (Plugin Development Kit) is meant for developing entire plugins on PHP E-Commerce platforms. If you're just looking to connect to our API without creating an entire plugin, you should check out our php [SDK].

## Requirements

- PHP >=7.4
- Composer

## Documentation

For examples, guides and in-depth information, visit our [Plugin Development Kit (PDK) documentation].

## Support

Create an issue or contact us via our [Developer Portal contact page].

## Contributing

View our [contribution guidelines] for information on how to contribute to the PDK.

### Prerequisites

- Node 18
- Yarn
- Docker

### Installation

Create `.env`:

```shell
cp .env.template .env
```

Install Yarn dependencies:

```shell
yarn
```

Install Composer dependencies:

```shell
docker compose up php
```

### Running tests

Run all tests:

```shell
docker compose run php composer test
```

### Linting

We use Prettier to format .json, .yml, .md and .html files.

Make sure Prettier is enabled in your IDE and runs on the following files:

```text
{**/*,*}.{md,html,yml,json}
```

Set up Git hooks to run Prettier on each commit, correcting any formatting issues.

```shell
yarn prepare
```

You can also run Prettier manually:

```shell
# Check formatting issues
yarn lint

# Fix formatting issues
yarn lint:fix
```

[Developer Portal contact page]: https://developer.myparcel.nl/contact.html
[Developer Portal]: https://developer.myparcel.nl
[SDK]: https://github.com/myparcelnl/sdk
[contribution guidelines]: https://github.com/myparcelnl/developer/blob/main/DEVELOPERS.md
[Plugin Development Kit (PDK) documentation]: https://developer.myparcel.nl/documentation/52.pdk/
