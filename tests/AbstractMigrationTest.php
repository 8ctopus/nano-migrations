<?php

declare(strict_types=1);

namespace Tests;

use Oct8pus\Migration\AbstractMigration;

/**
 * @internal
 *
 * @covers \Oct8pus\Migration\AbstractMigration
 */
final class AbstractMigrationTest extends TestCase
{
    private static string $migrationsFile;

    public static function setUpBeforeClass() : void
    {
        static::$migrationsFile = sys_get_temp_dir() . '/phpunit-migrations.txt';
        file_put_contents(static::$migrationsFile, '');
    }

    public function testOK() : void
    {
        (new MigrationMock(static::$migrationsFile, null))
            ->migrate(null)
            ->rollback(99);

        static::assertTrue(true);
    }
}

class MigrationMock extends AbstractMigration
{
    protected function query(string $sql) : self
    {
        return $this;
    }

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
