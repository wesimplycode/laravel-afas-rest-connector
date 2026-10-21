#!/usr/bin/env bash
#
# Run the test suite in Docker, without a local PHP or Composer installation.
#
# Usage:
#   ./docker-test.sh                      # PHP 8.4 (default)
#   ./docker-test.sh 8.1 8.4              # several PHP versions in a row
#   ./docker-test.sh 8.4 -- --filter OAuth  # pass arguments to phpunit
#
# For every PHP version a scratch copy of the package is created in .docker-test/php<version>/
# where Composer resolves the dependencies for that PHP version (so each version gets a matching
# Laravel / Testbench / PHPUnit release). Dependencies are only re-resolved when composer.json changes.
# Remove .docker-test/ to start from scratch.

set -euo pipefail
cd "$(dirname "$0")"

WORK=.docker-test
COMPOSER_IMAGE=${COMPOSER_IMAGE:-composer:2}
DEFAULT_PHP=8.4

versions=()
phpunit_args=()
while [ $# -gt 0 ]; do
    if [ "$1" = "--" ]; then
        shift
        phpunit_args=("$@")
        break
    fi
    versions+=("$1")
    shift
done
[ ${#versions[@]} -eq 0 ] && versions=("$DEFAULT_PHP")

# Run containers as the current user so vendor/ is not owned by root
docker_run() {
    docker run --rm -u "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer -e HOME=/tmp \
        -v "$PWD/$1":/app -w /app "${@:2}"
}

failed=()
for php in "${versions[@]}"; do
    dir="$WORK/php$php"
    echo
    echo "==> PHP $php"

    mkdir -p "$dir"
    rm -rf "$dir/src" "$dir/config" "$dir/tests"
    cp -r src config tests phpunit.xml "$dir"/

    if ! cmp -s composer.json "$dir/composer.json.orig"; then
        cp composer.json "$dir/composer.json"
        rm -f "$dir/composer.lock"
        docker_run "$dir" "$COMPOSER_IMAGE" sh -c \
            "composer config platform.php $php.0 && composer update --no-interaction --prefer-dist --no-progress --quiet && composer show 2>/dev/null | grep -E '^(laravel/framework|orchestra/testbench|phpunit/phpunit) '"
        cp composer.json "$dir/composer.json.orig"
    fi

    if docker_run "$dir" "php:$php-cli" vendor/bin/phpunit "${phpunit_args[@]}"; then
        :
    else
        failed+=("$php")
    fi
done

echo
if [ ${#failed[@]} -gt 0 ]; then
    echo "FAILED on PHP: ${failed[*]}"
    exit 1
fi
echo "All test runs passed (PHP: ${versions[*]})"
