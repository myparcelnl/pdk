# MyParcel Plugin Development Kit

[![Latest version](https://img.shields.io/github/v/release/myparcelnl/pdk)](https://github.com/myparcelnl/pdk/releases/latest)
[![Packagist Downloads](https://img.shields.io/packagist/dm/myparcelnl/pdk)](https://packagist.org/packages/myparcelnl/pdk)
[![Code quality](https://app.codacy.com/project/badge/Grade/c0f99d91962d448287b438b8162fff51)](https://www.codacy.com/gh/myparcelnl/pdk/dashboard?utm_source=github.com&utm_medium=referral&utm_content=myparcelnl/pdk&utm_campaign=Badge_Grade)
[![Code coverage](https://app.codacy.com/project/badge/Coverage/c0f99d91962d448287b438b8162fff51)](https://www.codacy.com/gh/myparcelnl/pdk/dashboard?utm_source=github.com&utm_medium=referral&utm_content=myparcelnl/pdk&utm_campaign=Badge_Coverage)
[![Chat with us](https://img.shields.io/badge/Slack-Chat%20with%20us-white?logo=slack&labelColor=4a154b)](https://join.slack.com/t/myparcel-dev/shared_invite/enQtNDkyNTg3NzA1MjM4LTM0Y2IzNmZlY2NkOWFlNTIyODY5YjFmNGQyYzZjYmQzMzliNDBjYzBkOGMwYzA0ZDYzNmM1NzAzNDY1ZjEzOTM)

This PDK is meant for developing entire plugins on PHP E-Commerce platforms. If you're just looking to connect to our API without creating an entire plugin, you should check out our php [SDK].

## Requirements

- PHP >=7.1
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

Create `.env.local`:

```shell
cp .env.local.template .env.local
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

Run all unit tests:

```shell
docker compose run php composer test
```

[Developer Portal contact page]: https://developer.myparcel.nl/contact.html
[Developer Portal]: https://developer.myparcel.nl
[SDK]: https://github.com/myparcelnl/sdk
[contribution guidelines]: https://github.com/myparcelnl/developer/blob/main/DEVELOPERS.md
[Plugin Development Kit (PDK) documentation]: https://developer.myparcel.nl/documentation/52.pdk/
