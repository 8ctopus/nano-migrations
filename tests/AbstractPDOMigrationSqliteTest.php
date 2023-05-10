<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\Migration\AbstractPDOMigration;
use PDO;

/**
 * @internal
 *
 * @covers \Oct8pus\Migration\AbstractPDOMigration
 */
final class AbstractPDOMigrationSqliteTest extends TestCase
{
    private static PDO $db;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($_ENV['DB_ENGINE'] === 'sqlite') {
            static::$db = new PDO("sqlite:memory", null, null, $options);
        } else {
            static::markTestSkipped('all tests in this file are invactive for this server configuration!');
        }

        static::$db->query('DROP TABLE IF EXISTS users');
        static::$db->query('DROP TABLE IF EXISTS user');
    }

    public function testOK() : void
    {
        $migration = (new SqliteMigrationMock(static::$migrationsFile, static::$db, null))
            ->migrate(null);

        $result = static::$db->query('.schema users');
        $output = $result->fetch();

        $expected = <<<SQL
        CREATE TABLE `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `email` varchar(40) NOT NULL,
          `firstName` varchar(255) NOT NULL,
          `password` varchar(40) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
        SQL;

        static::assertEquals($expected, $output['Create Table']);

        $migration->rollback(4);

        $result = static::$db->query('.schema user');
        $output = $result->fetch();

        $expected = <<<SQL
        CREATE TABLE `user` (
          `email` text NOT NULL,
          `password` text NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp()
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
        SQL;

        static::assertEquals($expected, $output['Create Table']);
    }

    public function testWithMigrateCountOK() : void
    {
        (new SqliteMigrationMock(static::$migrationsFile, static::$db, null))
            ->migrate(6)
            ->rollback(99);

        static::assertTrue(true);
    }
}

class SqliteMigrationMock extends AbstractPDOMigration
{
    protected function safetyCheck() : self
    {
        return $this;
    }

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
        return <<<'SQL'
            ALTER TABLE user
            ADD COLUMN firstName VARCHAR(255) NOT NULL
        SQL;
    }

    protected function down2() : string
    {
        return <<<'SQL'
            ALTER TABLE user
            DROP COLUMN firstName
        SQL;
    }

    protected function up3() : string
    {
        return <<<'SQL'
            ALTER TABLE user RENAME TO users
        SQL;
    }

    protected function down3() : string
    {
        return <<<'SQL'
            ALTER TABLE users RENAME TO user
        SQL;
    }

    protected function up4() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            ADD COLUMN lastName VARCHAR(40) NOT NULL
        SQL;
    }

    protected function down4() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            DROP COLUMN lastName
        SQL;
    }

    protected function up5() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            ADD COLUMN blocked BIT DEFAULT false
        SQL;
    }

    protected function down5() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            DROP COLUMN blocked
        SQL;
    }
}
