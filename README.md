# nano migration

[![packagist](http://poser.pugx.org/8ctopus/nano-migration/v)](https://packagist.org/packages/8ctopus/nano-migration)
[![downloads](http://poser.pugx.org/8ctopus/nano-migration/downloads)](https://packagist.org/packages/8ctopus/nano-migration)
[![min php version](http://poser.pugx.org/8ctopus/nano-migration/require/php)](https://packagist.org/packages/8ctopus/nano-migration)
[![license](http://poser.pugx.org/8ctopus/nano-migration/license)](https://packagist.org/packages/8ctopus/nano-migration)
[![tests](https://github.com/8ctopus/nano-migration/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/nano-migration/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/nano-migration/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/nano-migration/image-data/lines.svg)

A tiny database migration package

## demo

- git clone the repository
- run `composer install`
- create the migrations file `touch demo/migrations.txt`
- start Docker Desktop and `docker-compose up &`
- to migrate

    `php demo/index.php migrate [<count:int>]`

- to rollback

    `php demo/index.php rollback <count:int>`

## install

    composer require 8ctopus/nano-migration

You will need to extend `AbstractPDOMigration` class if you use php `PDO`. Extending the class requires implementing the `up` and `down` migration methods and the potential safety check. Refer to the demo directory example.

```php
final class Migration extends AbstractPDOMigration
{
    protected function up1() : string
    {
        return <<<'SQL'
        CREATE TABLE user (
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
    }

    protected function down1() : string
    {
        return <<<'SQL'
        DROP TABLE IF EXISTS user
        SQL;
    }

    protected function up2() : string
    {
        ...
    }

    protected function down2() : string
    {
        ...
    }

    /**
     * Safety check
     *
     * @return self
     *
     * @throws MigrationException
     */
    protected function safetyCheck() : self
    {
        $stdin = fopen('php://stdin', 'r', false);

        if ($stdin === false) {
            throw new MigrationException('fopen');
        }

        $this->logger?->warning('Confirm action (y/n): ');
        $input = trim(fgets($stdin));

        fclose($stdin);

        if ($input === 'y') {
            return $this;
        }

        throw new MigrationException('safety check abort');
    }
}
```

For other database engines, extend the `AbstractMigration` class and implement:

- `up` and `down` methods
- database connection
- database query

## clean code

    composer fix(-risky)

## phpstan

    composer phpstan

## phpmd

    composer phpmd
