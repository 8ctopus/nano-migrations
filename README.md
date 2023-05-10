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
- run `php demo/index.php migrate` to migrate
- run `php demo/index.php rollback 1` to rollback the last migration

### commands

    php demo/index.php migrate [<count:int>]

    php demo/index.php rollback <count:int>

## install

    composer require 8ctopus/nano-migration

You will need to extend the `AbstractMigration` class.

## clean code

    composer fix(-risky)

## phpstan

    composer phpstan

## phpmd

    composer phpmd
