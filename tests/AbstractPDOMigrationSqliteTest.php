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
            self::$db = new PDO('sqlite::memory:', null, null, $options);
        } else {
            self::markTestSkipped('all tests in this file are invactive for this server configuration!');
        }

        self::$db->query('DROP TABLE IF EXISTS users');
        self::$db->query('DROP TABLE IF EXISTS user');
    }

    public function testOK() : void
    {
        $migration = (new SqliteMigrationMock(self::$migrationsFile, self::$db, null))
            ->migrate(null);

        $result = self::$db->query("SELECT sql FROM sqlite_schema WHERE name='users'");
        $output = $result->fetch();

        $expected = <<<'SQL'
        CREATE TABLE "users" (
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        , firstName VARCHAR(255) NOT NULL, lastName VARCHAR(40) NOT NULL, blocked BIT DEFAULT false)
        SQL;

        self::assertSame($expected, $output['sql']);

        $migration->rollback(4);

        $result = self::$db->query("SELECT sql FROM sqlite_schema WHERE name='user'");
        $output = $result->fetch();

        $expected = <<<'SQL'
        CREATE TABLE "user" (
            email TEXT NOT NULL,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
        SQL;

        self::assertSame($expected, $output['sql']);
    }

    public function testWithMigrateCountOK() : void
    {
        $migration = (new SqliteMigrationMock(self::$migrationsFile, self::$db, null))
            ->migrate(6)
            ->rollback(99);

        self::assertSame(5, $migration->count());
    }
}

class SqliteMigrationMock extends AbstractPDOMigration
{
    protected function safetyCheck(array $methods) : self
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
