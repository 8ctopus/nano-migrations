<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static string $migrationsFile;

    public static function setUpBeforeClass() : void
    {
        static::$migrationsFile = sys_get_temp_dir() . '/phpunit-migrations.txt';
        file_put_contents(static::$migrationsFile, '');
    }
}
