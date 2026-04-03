# MyParcel Plugin Development Kit

[![Latest version](https://img.shields.io/github/v/release/myparcelnl/pdk)](https://github.com/myparcelnl/pdk/releases/latest)
[![Packagist Downloads](https://img.shields.io/packagist/dm/myparcelnl/pdk)](https://packagist.org/packages/myparcelnl/pdk)
[![Code coverage](https://img.shields.io/codecov/c/github/myparcelnl/pdk)](https://codecov.io/gh/myparcelnl/pdk)
![PHPStan](https://img.shields.io/badge/dynamic/yaml?url=https%3A%2F%2Fraw.githubusercontent.com%2Fmyparcelnl%2Fpdk%2Fmain%2Fphpstan.neon.dist&query=%24.parameters.level&label=PHPStan%20level&color=brightgreen)
![License](https://img.shields.io/github/license/myparcelnl/pdk)
[![Chat with us](https://img.shields.io/badge/Slack-Chat%20with%20us-white?logo=slack&labelColor=4a154b)](https://join.slack.com/t/myparcel-dev/shared_invite/enQtNDkyNTg3NzA1MjM4LTM0Y2IzNmZlY2NkOWFlNTIyODY5YjFmNGQyYzZjYmQzMzliNDBjYzBkOGMwYzA0ZDYzNmM1NzAzNDY1ZjEzOTM)

The MyParcel PDK (Plugin Development Kit) is meant for developing entire plugins on PHP E-Commerce platforms. If you're just looking to connect to our API without creating an entire plugin, you should check out our php [SDK].

## Requirements

- PHP 7.4 – 8.5
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

#### Testing on a specific PHP version

The default PHP version is 7.4. To test on a different version, change `PHP_VERSION` in `.env` and rebuild:

```shell
docker compose build
docker compose run php composer test
```

#### Updating snapshots

To update test snapshots and format them consistently:

```shell
yarn test:unit:snapshot
```

This runs the Pest snapshot update inside Docker, then applies Prettier formatting on the host. Use this instead of running `composer test:unit:snapshot` directly inside Docker, as PHP's `json_encode` outputs 4-space indented JSON while the project standard (enforced by Prettier) is 2-space.

### Adding a shipment option

Shipment options are managed through the `OrderOptionDefinitionInterface` system. Each option is a single Definition class that declares all its keys (shipment, capabilities, carrier settings, product settings, allow, price). All models, views, and services build their attributes and form elements dynamically from these definitions.

To add a new option:

1. **Create a Definition class** in `src/App/Options/Definition/` extending `AbstractOrderOptionDefinition`. Only two methods are required:
   - `getShipmentOptionsKey()` — the PDK-internal key, derived from `Str::camel(RefShipmentShipmentOptions::attributeMap()['sdk_key'])`
   - `getCapabilitiesOptionsKey()` — the V2 capabilities key, from `RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['capabilities_key']`
2. **Register it** in the `orderOptionDefinitions` array in `config/pdk-business-logic.php`.
3. **Optionally**, add a deprecated constant to `ShipmentOptions` if platform integrations reference the key directly.

Everything else (carrier settings, product settings, allow/price toggles, validation, frontend form fields, API export/import) is derived automatically. Run `yarn test:unit` to verify the consistency tests pass.

If the option is not yet in the SDK types, update the SDK or regenerate the OpenAPI types first.

> **Using Claude Code?** Run `/add-shipment-option` for a guided step-by-step walkthrough that asks the right questions and generates the code.

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
