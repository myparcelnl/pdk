# PHP Matrix Testing & Multi-Version Support — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the PDK testable on PHP 7.4 through 8.5 locally and in CI, then fix all compatibility issues.

**Architecture:** Parameterize the existing Docker setup with a `PHP_VERSION` build arg (default 7.4). Locally, developers set the env var to switch versions. In CI, only the unit test workflow runs a matrix across all 7 versions — other workflows stay on the default.

**Tech Stack:** Docker, Docker Compose, GitHub Actions, Pest (PHPUnit), Behat, PHPStan, Rector

---

## File Map

| File                                 | Action | Responsibility                                                     |
| ------------------------------------ | ------ | ------------------------------------------------------------------ |
| `Dockerfile`                         | Modify | Add `ARG PHP_VERSION=7.4`, parameterize `FROM`                     |
| `docker-compose.yml`                 | Modify | Add build arg, replace `IMAGE_NAME` with `pdk-${PHP_VERSION:-7.4}` |
| `.env.template`                      | Modify | Remove `IMAGE_NAME`, add `PHP_VERSION=7.4`                         |
| `README.md`                          | Modify | Add section on testing with different PHP versions                 |
| `.github/actions/setup/action.yml`   | Modify | Replace `IMAGE_NAME` with `PHP_VERSION` env var                    |
| `.github/workflows/--test-unit.yml`  | Modify | Add `php-version` input, update cache key                          |
| `.github/workflows/push.yml`         | Modify | Add matrix strategy to `test-unit` job                             |
| `.github/workflows/pull-request.yml` | Modify | Add matrix strategy to `test-unit` job                             |
| `src/**/*.php`                       | Modify | Compatibility fixes as discovered in Phase 3                       |

---

## Task 1: Parameterize Dockerfile

**Files:**

- Modify: `Dockerfile:1`

- [ ] **Step 1: Add PHP_VERSION build arg and parameterize FROM**

Replace the entire `Dockerfile` content with:

```dockerfile
ARG PHP_VERSION=7.4
FROM ghcr.io/myparcelnl/php-xd:${PHP_VERSION}-fpm-alpine

# install php zip extension
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-enable zip

# Increase PHP memory limit
RUN echo "memory_limit = 512M" >> /usr/local/etc/php/php.ini
```

- [ ] **Step 2: Verify default build still works**

Run:

```bash
docker compose build
```

Expected: Builds successfully using `php-xd:7.4-fpm-alpine` (the default).

---

## Task 2: Update docker-compose.yml

**Files:**

- Modify: `docker-compose.yml:1-6`

- [ ] **Step 1: Replace IMAGE_NAME with PHP_VERSION-based image name and add build arg**

Replace the `x-common` anchor with:

```yaml
x-common: &common
  build:
    context: .
    dockerfile: Dockerfile
    args:
      PHP_VERSION: ${PHP_VERSION:-7.4}
  image: pdk-${PHP_VERSION:-7.4}
```

The `services` section stays unchanged.

- [ ] **Step 2: Verify default build and run**

Run:

```bash
docker compose build
docker compose run php php -v
```

Expected: PHP 7.4.x output. Image tagged as `pdk-7.4`.

- [ ] **Step 3: Verify non-default version**

Run:

```bash
PHP_VERSION=8.4 docker compose build
PHP_VERSION=8.4 docker compose run php php -v
```

Expected: PHP 8.4.x output. Image tagged as `pdk-8.4`.

---

## Task 3: Update .env.template

**Files:**

- Modify: `.env.template:10-13`

- [ ] **Step 1: Replace IMAGE_NAME with PHP_VERSION**

Replace the Docker image section:

```
###
# Docker
###
PHP_VERSION=7.4
```

The API keys section at the top stays unchanged.

- [ ] **Step 2: Update local .env if it exists**

If `.env` exists locally, manually update it to replace `IMAGE_NAME=...` with `PHP_VERSION=7.4`. This file is gitignored so it won't be committed.

---

## Task 4: Update README.md

**Files:**

- Modify: `README.md:55-62`

- [ ] **Step 1: Add PHP version testing instructions**

After the existing "Running tests" section (`docker compose run php composer test`), add:

````markdown
#### Testing on a specific PHP version

The default PHP version is 7.4. To test on a different version:

```shell
PHP_VERSION=8.3 docker compose build
docker compose run php composer test
```
````

The build only needs to happen once per version. After that, `docker compose run` reuses the built image.

````

- [ ] **Step 2: Commit Phase 1 + 2 changes**

```bash
git add Dockerfile docker-compose.yml .env.template README.md
git commit -m "feat: parameterize PHP version in Docker setup

Add PHP_VERSION build arg to Dockerfile and docker-compose.yml.
Default remains 7.4. Override with PHP_VERSION env var.
Replace IMAGE_NAME with fixed pdk-{version} image naming."
````

---

## Task 5: Run tests on PHP 8.0

**Files:**

- Modify: `src/**/*.php` (as needed based on failures)

- [ ] **Step 1: Build PHP 8.0 image**

Run:

```bash
PHP_VERSION=8.0 docker compose build
```

- [ ] **Step 2: Run unit tests**

Run:

```bash
PHP_VERSION=8.0 docker compose run php composer test:unit
```

- [ ] **Step 3: Fix any failures**

Fix compatibility issues found. Common PHP 8.0 issues:

- Stricter type handling in internal functions
- `match` expression edge cases
- Named arguments changes

Keep fixes compatible with PHP 7.4.

- [ ] **Step 4: Verify 7.4 still passes**

Run:

```bash
docker compose run php composer test:unit
```

Expected: All tests pass on default 7.4.

- [ ] **Step 5: Commit fixes (if any)**

```bash
git add -A
git commit -m "fix: PHP 8.0 compatibility"
```

---

## Task 6: Run tests on PHP 8.1

**Files:**

- Modify: `src/**/*.php` (as needed based on failures)

- [ ] **Step 1: Build PHP 8.1 image**

Run:

```bash
PHP_VERSION=8.1 docker compose build
```

- [ ] **Step 2: Run unit tests**

Run:

```bash
PHP_VERSION=8.1 docker compose run php composer test:unit
```

- [ ] **Step 3: Fix any failures**

Common PHP 8.1 issues:

- Deprecation of passing null to non-nullable internal function parameters
- `FILTER_SANITIZE_STRING` removed
- Return type changes in internal methods

Keep fixes compatible with PHP 7.4.

- [ ] **Step 4: Verify 7.4 and 8.0 still pass**

Run:

```bash
docker compose run php composer test:unit
PHP_VERSION=8.0 docker compose run php composer test:unit
```

- [ ] **Step 5: Commit fixes (if any)**

```bash
git add -A
git commit -m "fix: PHP 8.1 compatibility"
```

---

## Task 7: Run tests on PHP 8.2

**Files:**

- Modify: `src/**/*.php` (as needed based on failures)

- [ ] **Step 1: Build PHP 8.2 image**

Run:

```bash
PHP_VERSION=8.2 docker compose build
```

- [ ] **Step 2: Run unit tests**

Run:

```bash
PHP_VERSION=8.2 docker compose run php composer test:unit
```

- [ ] **Step 3: Fix any failures**

Common PHP 8.2 issues:

- Dynamic properties deprecated (add `#[AllowDynamicProperties]` with polyfill or refactor to declared properties)
- `readonly` class restrictions
- `utf8_encode`/`utf8_decode` deprecated

Keep fixes compatible with PHP 7.4.

- [ ] **Step 4: Verify 7.4, 8.0, 8.1 still pass**

Run:

```bash
docker compose run php composer test:unit
PHP_VERSION=8.0 docker compose run php composer test:unit
PHP_VERSION=8.1 docker compose run php composer test:unit
```

- [ ] **Step 5: Commit fixes (if any)**

```bash
git add -A
git commit -m "fix: PHP 8.2 compatibility"
```

---

## Task 8: Run tests on PHP 8.3

**Files:**

- Modify: `src/**/*.php` (as needed based on failures)

- [ ] **Step 1: Build PHP 8.3 image**

Run:

```bash
PHP_VERSION=8.3 docker compose build
```

- [ ] **Step 2: Run unit tests**

Run:

```bash
PHP_VERSION=8.3 docker compose run php composer test:unit
```

- [ ] **Step 3: Fix any failures**

Common PHP 8.3 issues:

- More restrictive type coercion
- `Serializable` interface deprecated
- Changes to `range()` behavior

Keep fixes compatible with PHP 7.4.

- [ ] **Step 4: Verify all previous versions still pass**

Run:

```bash
docker compose run php composer test:unit
PHP_VERSION=8.0 docker compose run php composer test:unit
PHP_VERSION=8.1 docker compose run php composer test:unit
PHP_VERSION=8.2 docker compose run php composer test:unit
```

- [ ] **Step 5: Commit fixes (if any)**

```bash
git add -A
git commit -m "fix: PHP 8.3 compatibility"
```

---

## Task 9: Run tests on PHP 8.4

**Files:**

- Modify: `src/**/*.php` (as needed based on failures)

- [ ] **Step 1: Build PHP 8.4 image**

Run:

```bash
PHP_VERSION=8.4 docker compose build
```

- [ ] **Step 2: Run unit tests**

Run:

```bash
PHP_VERSION=8.4 docker compose run php composer test:unit
```

- [ ] **Step 3: Fix any failures**

Common PHP 8.4 issues:

- Implicit nullable parameter types deprecated
- Changes to `round()` behavior
- `E_STRICT` constant deprecated

Keep fixes compatible with PHP 7.4.

- [ ] **Step 4: Verify all previous versions still pass**

Run:

```bash
docker compose run php composer test:unit
PHP_VERSION=8.0 docker compose run php composer test:unit
PHP_VERSION=8.1 docker compose run php composer test:unit
PHP_VERSION=8.2 docker compose run php composer test:unit
PHP_VERSION=8.3 docker compose run php composer test:unit
```

- [ ] **Step 5: Commit fixes (if any)**

```bash
git add -A
git commit -m "fix: PHP 8.4 compatibility"
```

---

## Task 10: Run tests on PHP 8.5

**Files:**

- Modify: `src/**/*.php` (as needed based on failures)

- [ ] **Step 1: Build PHP 8.5 image**

Run:

```bash
PHP_VERSION=8.5 docker compose build
```

- [ ] **Step 2: Run unit tests**

Run:

```bash
PHP_VERSION=8.5 docker compose run php composer test:unit
```

- [ ] **Step 3: Fix any failures**

PHP 8.5 is in development — new deprecations and behavior changes may appear. Fix as needed while keeping 7.4 compatibility.

- [ ] **Step 4: Verify all previous versions still pass**

Run:

```bash
docker compose run php composer test:unit
PHP_VERSION=8.0 docker compose run php composer test:unit
PHP_VERSION=8.1 docker compose run php composer test:unit
PHP_VERSION=8.2 docker compose run php composer test:unit
PHP_VERSION=8.3 docker compose run php composer test:unit
PHP_VERSION=8.4 docker compose run php composer test:unit
```

- [ ] **Step 5: Commit fixes (if any)**

```bash
git add -A
git commit -m "fix: PHP 8.5 compatibility"
```

---

## Task 11: Update CI — test-unit workflow

**Files:**

- Modify: `.github/workflows/--test-unit.yml:1-44`

- [ ] **Step 1: Add php-version input and update the workflow**

Replace the entire `--test-unit.yml` with:

```yaml
name: '♻️ Run unit tests'

on:
  workflow_call:
    inputs:
      php-version:
        description: 'PHP version'
        required: true
        type: string

jobs:
  test-unit:
    runs-on: ubuntu-22.04
    steps:
      - uses: actions/checkout@v6

      - name: 'Handle coverage cache'
        uses: actions/cache@v5
        id: coverage-cache
        with:
          path: ./clover.xml
          key: coverage-clover-${{ inputs.php-version }}-${{ hashFiles('**/composer.lock', './src/**', './config/**', './tests/**', './.github/workflows/--test-unit.yml') }}

      - uses: ./.github/actions/setup
        if: steps.coverage-cache.outputs.cache-hit != 'true'
        with:
          token: ${{ secrets.GITHUB_TOKEN }}
          php-version: ${{ inputs.php-version }}

      - name: 'Run unit tests'
        if: steps.coverage-cache.outputs.cache-hit != 'true'
        shell: bash
        env:
          PHP_VERSION: ${{ inputs.php-version }}
        #language=bash
        run: |
          docker compose run php \
            php -dmemory_limit=512M -dpcov.enabled=1 \
            vendor/bin/pest \
              --colors=always \
              --coverage-clover=clover.xml \
              --no-interaction

          # Strip the /app/ prefix from the coverage paths before uploading.
          sed -i 's/\/app\///g' clover.xml

      - uses: codecov/codecov-action@v5
        continue-on-error: true
        with:
          token: ${{ secrets.CODECOV_TOKEN }}
```

---

## Task 12: Update CI — setup action

**Files:**

- Modify: `.github/actions/setup/action.yml:42-52`

- [ ] **Step 1: Replace IMAGE_NAME with PHP_VERSION in the setup action**

Replace the "Install composer dependencies" step (lines 42-52):

```yaml
- name: 'Install composer dependencies'
  shell: bash
  env:
    PHP_VERSION: ${{ inputs.php-version }}
  #language=bash
  run: |
    docker compose build
    docker compose run \
      --volume $HOME/.composer:/root/.composer \
      php \
      composer update --no-progress --no-scripts --no-plugins
```

Note: The `build-docker-image` step (line 16-19) may also need updating to pass the `PHP_VERSION` build arg. If the `myparcelnl/actions/build-docker-image@v4` action doesn't support build args, replace it with the `docker compose build` above (which already handles the arg via docker-compose.yml).

---

## Task 13: Update CI — caller workflows

**Files:**

- Modify: `.github/workflows/push.yml:21-24`
- Modify: `.github/workflows/pull-request.yml:16-19`

- [ ] **Step 1: Add matrix strategy to test-unit in push.yml**

Replace the `test-unit` job (lines 21-24) in `push.yml`:

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

- [ ] **Step 2: Add matrix strategy to test-unit in pull-request.yml**

Replace the `test-unit` job (lines 16-19) in `pull-request.yml`:

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

- [ ] **Step 3: Commit CI changes**

```bash
git add .github/
git commit -m "feat: add PHP version matrix to unit test CI workflow

Run unit tests on PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, 8.5.
Other workflows remain on vars.PHP_VERSION default."
```
