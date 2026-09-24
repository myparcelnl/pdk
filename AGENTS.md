# MyParcel PDK (PHP)

The plugin development kit is a composer dependency of several MyParcel e-commerce plugins. It provides the PHP integration between the plugins and the MyParcel API: data models, settings, business logic, the context and UI definitions that js-pdk renders, and testing utilities.

## MyParcel stack

This repository is one part of the MyParcel plugin stack:

| Repository                                                                    | Role                                                                                                                      |
| ----------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| [myparcelnl/sdk](https://github.com/myparcelnl/sdk)                           | PHP client generated from the MyParcel API OpenAPI spec. Source of carriers, delivery types, package types and API types. |
| [myparcelnl/pdk](https://github.com/myparcelnl/pdk) **(this repository)**     | PHP Plugin Development Kit. Business logic, models, settings, migrations and API calls shared by all plugins.             |
| [myparcelnl/js-pdk](https://github.com/myparcelnl/js-pdk)                     | JS Plugin Development Kit. Admin UI and checkout scripts that the plugins build on.                                       |
| [myparcelnl/delivery-options](https://github.com/myparcelnl/delivery-options) | Checkout widget in which the consumer picks a delivery or pickup option.                                                  |
| [myparcelnl/woocommerce](https://github.com/myparcelnl/woocommerce)           | WooCommerce plugin. Thin adapter on top of the PDK.                                                                       |
| [myparcelnl/prestashop](https://github.com/myparcelnl/prestashop)             | PrestaShop module. Thin adapter on top of the PDK.                                                                        |

How they connect:

- A plugin bootstraps the PDK and implements the platform adapters (storage, hooks, rendering, cron). Behaviour that all plugins share goes in the PDK, not in one plugin.
- The PDK renders its context as JSON in an HTML attribute (`data-pdk-context` for the admin). The js-pdk apps read it and call PDK endpoint actions (registered in the PDK's `config/actions.php`) for more data.
- The delivery options widget is not bundled into the plugins. The PDK builds jsdelivr CDN URLs for `myparcel.js`, `myparcel.lib.js` and `style.css` (the PDK's `config/pdk-dependencies.php`), and the plugin loads them at runtime. In js-pdk, the `@myparcel-dev/delivery-options` npm dependency supplies only types and constants.
- The widget and the js-pdk admin get carrier capabilities by POSTing to the PDK `proxyCapabilities` action (`PdkCapabilitiesActions`).
- Carriers, delivery types and package types come from the SDK. Do not add definitions of them to the PDK or the plugins.

For local development, `pdk-dev-on` links local checkouts of the PDK and js-pdk into a plugin. It adds a composer `path` repository to `composer.json` and yarn `portal:` links to `package.json`. Never commit these changes. Suggest it only when the task needs changes in the PDK or js-pdk, or needs to test such changes in a plugin. MyParcel employees can find the setup in [Local PDK in PDK plugin](https://myparcelnl.atlassian.net/wiki/spaces/IG/pages/319422473).

The delivery options widget has no link command. To test a local build, serve `apps/delivery-options/dist` from a delivery-options checkout and override `deliveryOptionsCdnUrlJs`, `deliveryOptionsCdnUrlJsLib` and `deliveryOptionsCdnUrlCss` in the plugin's `config/pdk.php`. Never commit these overrides. This works only locally: there is no way to give a tester a plugin build with a custom widget version.

To find the local checkouts of the other repositories, read `.claude/int-branch.json` in a parent directory. Its `repos` list holds their paths. If there is none and the task needs another repository, tell the user once that the `int-branch` skill of the `external-integrations-basekit` plugin offers to write this file on its first run.

## MyParcel employees: agent plugin

If no skills from the `external-integrations-basekit` plugin are available in this session, tell the user once, at the start, that the plugin is missing and give these commands:

```
/plugin marketplace add mypadev/ai-plugins
/plugin install external-integrations-basekit@myparcel-claude-code-marketplace
```

The marketplace is private, so only MyParcel employees can install it.

## PHP constraints

- Retain PHP 7.4 compatibility
- Include suggestions for PHP 8.0+ enhancements with a "@TODO:" prefix marker in docblocks or inline comments for future reference
- Use strict types

## Testing

- Use Pest for testing
- Remember we use Pest v1, do not suggest features from Pest v2 or later
- Avoid usage of snapshots, concrete assertions SHOULD be used instead
- Snapshots MAY still be used to assert the shape and contents of objects, API requests and responses, but not in situations where specific values are important to assert on (e.g., asserting that a specific carrier is used, or that a specific error message is returned). In those cases, write concrete assertions instead of relying on snapshots.
- Avoid testing specific carriers, focus on testing capabilities and expectations based on it (like available package types, delivery types, shipment options etc.)
- Write Mocks using existing PDK custom mocking utilities
- Run all tests through docker via composer so that the bootstrap/prepend file is included: `yarn run test` or `yarn run test:unit`
- Update snapshots with `yarn test:unit:snapshot`. Snapshots are Prettier-ignored (see `.prettierignore`) so their formatting is owned by the snapshot writer and not reformatted on save.
- To run a specific test or filter: `docker compose run php composer test -- --filter="test name"`
- When switching PHP versions, always run `composer update` first: `PHP_VERSION=8.4 docker compose run php composer update --no-interaction --no-progress`
- When encountering test failures, check whether `main` fails the same way. A failure that also exists on `main` is unrelated and may be ignored for the task. A failure the current branch introduced is in scope, even when the task at hand did not cause it: fix it in the same PR.

## Breaking change considerations

- Consider the changes in git staged or unstaged files as acceptable breaking changes

## Shell commands

- Consider that the project is using Docker for development, so any shell commands should be run through Docker when applicable (e.g., `docker compose run php`)

## Debugging

- When asked a question that requires information about the codebase, analyze by adding debugging statements (e.g., `print_r`, `var_dump`, etc.) or using breakpoints and executing sections of code or relevant tests. Rather than analyzing the codebase without executing it. This is especially important when the question is about dynamic data or behavior that cannot be easily inferred from static code analysis alone.
- When solving a bug, always write a test that fails under the current conditions before implementing the fix, and then implement the fix to make the test pass.
- Ensure debug output is visible with the verbosity level of the command being used to run the tests (e.g., `-v` for verbose mode in PHPUnit/Pest). If necessary, adjust the verbosity level to ensure that debug output is displayed.
- When a root cause is identified, immediately output any findings to the user

## Carriers

- Carrier models should adhere to a "single source of truth" principle. The Carrier model should always be fetched through the CarrierRepository, and set through the Account model. Consider the Carrier attributes on any other Model than shop to be read-only.
- The PDK should not contain business logic related to specific carriers, platforms or countries. It may only base this logic on external and dynamic input from the API or proposition configuration.

## Endpoints

- New endpoints and major refactors must use the definitions as available in the generated clients in the SDK
- Treat API requests and responses defined within the PDK as legacy and deprecated

## Migrations

- Follow "Writing a migration" in `README.md`. Do not add new `UpgradeMigrationInterface` migrations.
- Installation migrations (`InstallationMigrationInterface`) set up initial state on a fresh install.

## Cache

The .cache folder may contain artifacts related to a specific plugin instance when linked to a plugin, you can safely clear the `.cache` folder when making changes to the PDK or encountering unexpected behavior from plugins.
