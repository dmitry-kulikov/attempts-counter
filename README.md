# attempts-counter

An abstract PHP counter of attempts to perform some actions.

[![License](https://poser.pugx.org/kdn/attempts-counter/license)](https://packagist.org/packages/kdn/attempts-counter)
[![Latest Stable Version](https://poser.pugx.org/kdn/attempts-counter/v/stable)](https://packagist.org/packages/kdn/attempts-counter)
[![Code Coverage](https://scrutinizer-ci.com/g/dmitry-kulikov/attempts-counter/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dmitry-kulikov/attempts-counter/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dmitry-kulikov/attempts-counter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dmitry-kulikov/attempts-counter/?branch=master)
[![Code Climate](https://codeclimate.com/github/dmitry-kulikov/attempts-counter/badges/gpa.svg)](https://codeclimate.com/github/dmitry-kulikov/attempts-counter)

## Requirements

- PHP 5.4 or later or HHVM 3.

## Installation

The preferred way to install this package is through [Composer](https://getcomposer.org).

To install, either run

```sh
php composer.phar require kdn/attempts-counter "*"
```

or add

```text
"kdn/attempts-counter": "*"
```

to the `require` section of your `composer.json` file.

## Usage

Repeat the retrieving of URL content in case of network problems:

```php
<?php

use kdn\attemptsCounter\Action;

$contents = false;
$action = new Action('query-url', 10, 3 * 10 ** 9); // up to 10 attempts, 3 seconds delay between attempts
while ($contents === false) {
    $contents = @file_get_contents('https://unreliable.site');
    $action->increment();
}
var_dump($contents);
```

A more sophisticated example illustrating failures for various reasons,
the maximum number of attempts and the delay between attempts can be configured separately:

```php
<?php

use kdn\attemptsCounter\Action;
use kdn\attemptsCounter\AttemptsCounter;

function generateId() {
    return mt_rand(0, 30);
}

$ids = [];
$counter = new AttemptsCounter();
$counter->addAction(new Action('generate-unique', 5))
    ->addAction(new Action('generate-odd', 10));
while (count($ids) < 10) {
    $id = generateId();
    if ($id % 2 === 0) {
        $counter->getAction('generate-odd')->increment();
        continue;
    }
    if (in_array($id, $ids, true)) {
        $counter->getAction('generate-unique')->increment();
        continue;
    }
    $ids[] = $id;
}
var_dump($ids);
```

For further details, please see the documentation for the public methods in the
[Action](https://github.com/dmitry-kulikov/attempts-counter/blob/master/src/Action.php)
and
[AttemptsCounter](https://github.com/dmitry-kulikov/attempts-counter/blob/master/src/AttemptsCounter.php) classes.

## Testing

Make sure you installed all Composer dependencies (run `composer update` in the base directory of repository).
Run PHPUnit in the base directory of repository:

```sh
./vendor/bin/phpunit
```

### Testing using Docker

#### Requirements

- Docker >= 19.03.0 ([install](https://docs.docker.com/get-docker));
- Docker Compose >= 1.25.5 ([install](https://docs.docker.com/compose/install));
- Docker plugins:
  - buildx ([install](https://github.com/docker/buildx#installing)).

#### Up and running

1. Provide credentials for Composer:

   ```sh
   cp auth.json.example \
       auth.json
   ```

   I suggest to set GitHub OAuth token (also known as personal access token) in `auth.json`,
   however if you have doubts about security, or you are lazy to generate token then you can replace content of
   `auth.json` on `{}`, in most cases this will work.

1. Build images for services:

   ```sh
   docker buildx bake --load --pull
   ```

   or

   ```sh
   docker buildx bake --load --pull --no-cache --progress plain
   ```

   see `docker buildx bake --help` for details.

1. Start service in background mode:

   ```sh
   docker-compose up --detach 8.0
   ```

   This command will start the service with PHP 8.0. Also allowed `7.4` and `5.6`, see services
   defined in `docker-compose.yml`.

1. Execute tests in the running container:

   ```sh
   docker-compose exec 8.0 ./vendor/bin/phpunit
   ```

   Alternatively you can start a shell in the running container and execute tests from it:

   ```sh
   docker-compose exec 8.0 bash
   $ ./vendor/bin/phpunit
   ```

1. Stop and remove containers created by `up`:

   ```sh
   docker-compose down
   ```

   You may want to remove volumes along with containers:

   ```sh
   docker-compose down --volumes
   ```
