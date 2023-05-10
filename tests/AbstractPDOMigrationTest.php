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
final class AbstractPDOMigrationTest extends TestCase
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

        if ($_ENV['DB_ENGINE'] === 'mysql') {
            static::$db = new PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8", $_ENV['DB_USER'], $_ENV['DB_PASS'], $options);
        } else {
            static::markTestSkipped('all tests in this file are invactive for this server configuration!');
        }

        static::$db->query('DROP TABLE IF EXISTS users');
        static::$db->query('DROP TABLE IF EXISTS user');
    }

    public function testOK() : void
    {
        $migration = (new PDOMigrationMock(static::$migrationsFile, static::$db, null))
            ->migrate(null);

        $result = static::$db->query('SHOW CREATE TABLE users');
        $output = $result->fetch();

        $expected = <<<'SQL'
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

        $result = static::$db->query('SHOW CREATE TABLE user');
        $output = $result->fetch();

        $expected = <<<'SQL'
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
        (new PDOMigrationMock(static::$migrationsFile, static::$db, null))
            ->migrate(6)
            ->rollback(99);

        static::assertTrue(true);
    }
}

class PDOMigrationMock extends AbstractPDOMigration
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
            ADD COLUMN firstName VARCHAR(255) NOT NULL AFTER email
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
            ALTER TABLE user RENAME users
        SQL;
    }

    protected function down3() : string
    {
        return <<<'SQL'
            ALTER TABLE users RENAME user
        SQL;
    }

    protected function up4() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY AFTER email,
            MODIFY COLUMN email TEXT NOT NULL AFTER id
        SQL;
    }

    protected function down4() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            DROP COLUMN id
        SQL;
    }

    protected function up5() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            MODIFY COLUMN email VARCHAR(40) NOT NULL,
            MODIFY COLUMN password VARCHAR(40) NOT NULL
        SQL;
    }

    protected function down5() : string
    {
        return <<<'SQL'
            ALTER TABLE users
            MODIFY COLUMN email TEXT NOT NULL,
            MODIFY COLUMN password TEXT NOT NULL
        SQL;
    }
}
