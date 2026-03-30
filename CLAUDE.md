# General constraints

- Avoid writing prompt instructions or reasoning into code comments. Instead, keep comments focused on explaining the code itself. If you need to include reasoning or instructions for yourself, consider using inline comments with a "@TODO:" prefix marker to indicate that they are temporary notes for future reference.

# PHP constraints

- Retain PHP 7.4 compatibility
- Include suggestions for PHP 8.0+ enhancements with a "@TODO:" prefix marker in docblocks or inline comments for future reference
- Use strict types

# Testing

- Use Pest for testing
- Remember we use Pest v1, do not suggest features from Pest v2 or later
- Avoid usage of snapshots, concrete assertions SHOULD be used instead
- Snapshots MAY still be used to assert the shape and contents of objects, API requests and responses, but not in situations where specific values are important to assert on (e.g., asserting that a specific carrier is used, or that a specific error message is returned). In those cases, write concrete assertions instead of relying on snapshots.
- Avoid testing specific carriers, focus on testing capabilities and expectations based on it (like available package types, delivery types, shipment options etc.)
- Match class names to actual class names in the codebase
- Write Mocks using existing PDK custom mocking utilities
- Run all tests through docker: `docker compose run php`
- When encountering test failures, consider whether the failure is related to the users prompt or is an unrelated issue. If the failure is unrelated, it is be acceptable to ignore the failure for the purpose of this task.

# Breaking change considerations

- Consider the changes in git staged or unstaged files as acceptable breaking changes

# Shell commands

- Consider the host machine is Mac OSX when using local shell commands
- Consider that the project is using Docker for development, so any shell commands should be run through Docker when applicable (e.g., `docker compose run php`)

# Debugging

- When asked a question that requires information about the codebase, analyze by adding debugging statements (e.g., `print_r`, `var_dump`, etc.) or using breakpoints and executing sections of code or relevant tests. Rather than analyzing the codebase without executing it. This is especially important when the question is about dynamic data or behavior that cannot be easily inferred from static code analysis alone.
- When solving a bug, always write a test that fails under the current conditions before implementing the fix, and then implement the fix to make the test pass. This ensures that the bug is properly addressed and prevents regressions in the future.
- Ensure debug output is visible with the verbosity level of the command being used to run the tests (e.g., `-v` for verbose mode in PHPUnit/Pest). If necessary, adjust the verbosity level to ensure that debug output is displayed.
- When a root cause is identified, immediately output any findings to the user

# Carriers

- Carrier models should adhere to a "single source of truth" principle. The Carrier model should always be fetched through the CarrierRepository, and set through the Account model. This ensures that all carrier data is consistent and up-to-date across the application. Consider the Carrier attributes on any other Model than shop to be read-only.
- Carriers, ShipmentOptions, DeliveryTypes should be based on API-based definitions in the SDK, and not be hardcoded in the PDK.
- The PDK should not contain business logic related to specific carriers, platforms or countries. It may only base this logic on external and dynamic input from the API or proposition configuration.

# Endpoints

- New endpoints and major refactors must use the definitions as available in the generated clients in the SDK
- Treat API requests and responses defined within the PDK as legacy and deprecated
