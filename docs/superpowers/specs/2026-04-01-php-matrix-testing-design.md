# PHP Matrix Testing & Multi-Version Support

## Goal

Ensure the PDK runs correctly on all PHP minor versions from 7.4 through 8.5. Tests, static analysis, and quality checks must pass on every version — both locally and in CI.

## PHP Versions

All minor versions between 7.4 and 8.5:

- 7.4 (default, EOL)
- 8.0 (EOL)
- 8.1 (EOL)
- 8.2 (security-only)
- 8.3 (active)
- 8.4 (latest stable)
- 8.5 (in development)

The minimum supported version remains 7.4. No new language features from higher versions should be adopted — fixes go in the direction of making existing code compatible with newer PHP versions.

## Phase 1: Docker Parameterization

### Dockerfile

Add a `PHP_VERSION` build arg that defaults to `7.4`:

```dockerfile
ARG PHP_VERSION=7.4
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION}-fpm-alpine
```

The rest of the Dockerfile (zip extension, memory limit) stays unchanged.

### docker-compose.yml

Pass the build arg through and use a fixed image naming scheme. Remove `IMAGE_NAME` entirely:

```yaml
x-common: &common
  build:
    context: .
    dockerfile: Dockerfile
    args:
      PHP_VERSION: ${PHP_VERSION:-7.4}
  image: pdk-${PHP_VERSION:-7.4}
```

Remove `IMAGE_NAME` from `.env.template`. Add `PHP_VERSION=7.4` instead.

### Local Usage

```bash
# Default (7.4)
docker compose build
docker compose run php composer test

# Specific version
PHP_VERSION=8.3 docker compose build
docker compose run php composer test
```

The build only needs to happen once per version. After that, `docker compose run` reuses the built image.

## Phase 2: README Update

Add a section to README.md documenting how to test on a specific PHP version locally:

```bash
# Test on a specific PHP version
PHP_VERSION=8.3 docker compose build
docker compose run php composer test
```

## Phase 3: Fix Compatibility Issues

Run the unit test suite locally against each PHP version (7.4 through 8.5) and fix all failures before pushing to CI. Expected issue categories by version:

- **PHP 8.0+**: Stricter type handling, named arguments in internal functions, `match` vs `switch`
- **PHP 8.1+**: Deprecation of passing null to non-nullable internal function parameters, enum adoption in dependencies, fiber-related changes
- **PHP 8.2+**: Dynamic properties deprecated (`#[AllowDynamicProperties]` or refactor), `readonly` class restrictions
- **PHP 8.3+**: More restrictive type coercion, typed class constants in dependencies
- **PHP 8.4+**: Implicit nullable parameter deprecation, property hooks in dependencies
- **PHP 8.5**: New deprecations (in development, subject to change)

Fixes should maintain 7.4 compatibility — no adopting newer syntax. Common fix patterns:

- Add explicit null checks where PHP 8.1+ deprecates implicit null passing
- Add `#[AllowDynamicProperties]` attribute (with polyfill) where needed for 8.2+
- Update PHPStan baseline per version if needed
- Update dependency constraints in composer.json if a dependency doesn't support certain PHP versions

## Phase 4: CI Matrix

### Scope

Only `--test-unit.yml` runs through the PHP version matrix. All other workflows (`--setup.yml`, `--test-integration.yml`, `--analyse.yml`, `--quality.yml`) remain on `${{ vars.PHP_VERSION }}` (default 7.4). This keeps CI fast while still catching version-specific compatibility issues where it matters most.

### test-unit Workflow

`--test-unit.yml` needs:

1. A `php-version` input (replacing `${{ vars.PHP_VERSION }}` references)
2. Coverage cache key updated to use the input value
3. `PHP_VERSION` env var set for `docker compose` commands

```yaml
on:
  workflow_call:
    inputs:
      php-version:
        description: 'PHP version'
        required: true
        type: string
```

### Cache Keys

Only the test-unit coverage cache key changes:

| Workflow          | Cache    | Key change                                 |
| ----------------- | -------- | ------------------------------------------ |
| `--test-unit.yml` | Coverage | `vars.PHP_VERSION` -> `inputs.php-version` |
| All others        | Various  | Unchanged — keep using `vars.PHP_VERSION`  |

### Setup Action

Remove the `IMAGE_NAME` env var. Set `PHP_VERSION` instead so docker-compose resolves the image name:

```yaml
- name: 'Install composer dependencies'
  shell: bash
  env:
    PHP_VERSION: ${{ inputs.php-version }}
  run: |
    docker compose build
    docker compose run \
      --volume $HOME/.composer:/root/.composer \
      php \
      composer update --no-progress --no-scripts --no-plugins
```

The `build-docker-image` action may need to be replaced or updated to pass `PHP_VERSION` as a build arg. If it doesn't support build args, replace it with `docker compose build` directly.

### Caller Workflows

Both `push.yml` and `pull-request.yml` add a matrix strategy to the `test-unit` job only:

```yaml
test-unit:
  needs: setup
  strategy:
    matrix:
      php-version: ['7.4', '8.0', '8.1', '8.2', '8.3', '8.4', '8.5']
    fail-fast: false
  uses: ./.github/workflows/--test-unit.yml
  with:
    php-version: ${{ matrix.php-version }}
  secrets: inherit
```

`fail-fast: false` ensures all versions run even when one fails. All other jobs remain unchanged.

## Files Changed

| File                                 | Change                                                                        |
| ------------------------------------ | ----------------------------------------------------------------------------- |
| `Dockerfile`                         | Add `ARG PHP_VERSION=7.4`, parameterize `FROM`                                |
| `docker-compose.yml`                 | Add build arg, change image to `pdk-${PHP_VERSION:-7.4}`, remove `IMAGE_NAME` |
| `.env.template`                      | Remove `IMAGE_NAME`, add `PHP_VERSION=7.4`                                    |
| `.github/actions/setup/action.yml`   | Replace `IMAGE_NAME` with `PHP_VERSION`, add `docker compose build`           |
| `.github/workflows/--test-unit.yml`  | Add `php-version` input, update cache key, pass to setup action               |
| `.github/workflows/push.yml`         | Add matrix strategy to `test-unit` job, pass `php-version`                    |
| `.github/workflows/pull-request.yml` | Add matrix strategy to `test-unit` job, pass `php-version`                    |
| `README.md`                          | Add section on testing with different PHP versions                            |
| `src/**/*.php`                       | Compatibility fixes as discovered                                             |
