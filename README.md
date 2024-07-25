# nano migrations

[![packagist](https://poser.pugx.org/8ctopus/nano-migrations/v)](https://packagist.org/packages/8ctopus/nano-migrations)
[![downloads](https://poser.pugx.org/8ctopus/nano-migrations/downloads)](https://packagist.org/packages/8ctopus/nano-migrations)
[![min php version](https://poser.pugx.org/8ctopus/nano-migrations/require/php)](https://packagist.org/packages/8ctopus/nano-migrations)
[![license](https://poser.pugx.org/8ctopus/nano-migrations/license)](https://packagist.org/packages/8ctopus/nano-migrations)
[![tests](https://github.com/8ctopus/nano-migrations/actions/workflows/tests.yml/badge.svg)](https://github.com/8ctopus/nano-migrations/actions/workflows/tests.yml)
![code coverage badge](https://raw.githubusercontent.com/8ctopus/nano-migrations/image-data/coverage.svg)
![lines of code](https://raw.githubusercontent.com/8ctopus/nano-migrations/image-data/lines.svg)

A tiny database migrations package

Migrations are used to manage database schema changes over time. The rationale behind migrations is to make it easier to maintain and evolve the database schema as the application evolves. This package makes it easy to deal with migrations in small projects not using a framework.

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

    composer require 8ctopus/nano-migrations

You will need to extend `AbstractPDOMigration` class if you use php `PDO`. Extending the class requires implementing the `up` and `down` migration methods and the potential safety check. Refer to the demo directory example.

```php
use Oct8pus\Migration\AbstractPDOMigration;

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
     * @param array $methods
     *
     * @return self
     *
     * @throws MigrationException
     */
    protected function safetyCheck(array $methods) : self
    {
        $stdin = fopen('php://stdin', 'r', false);

        if ($stdin === false) {
            throw new MigrationException('fopen');
        }

        $this->logger?->info('migrations to run:');

        foreach ($methods as $method) {
            $this->logger?->info("- {$method}");
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
