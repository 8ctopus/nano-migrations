<?php

declare(strict_types=1);

namespace Tests;

use Apix\Log\Logger\Runtime;
use Exception;
use Oct8pus\Migration\AbstractMigration;
use Oct8pus\Migration\MigrationException;

/**
 * @internal
 *
 * @covers \Oct8pus\Migration\AbstractMigration
 */
final class AbstractMigrationTest extends TestCase
{
    public function testOK() : void
    {
        $migration = (new MigrationMock(self::$migrationsFile, null))
            ->migrate(null)
            ->rollback(99);

        self::assertSame(5, $migration->count());
    }

    public function testWithMigrateCountOK() : void
    {
        $migration = (new MigrationMock(self::$migrationsFile, null))
            ->migrate(6)
            ->rollback(99);

        self::assertSame(5, $migration->count());
    }

    public function testMigrationsCount() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('migration count must be greater than zero');

        (new MigrationMock(self::$migrationsFile, null))
            ->migrate(0)
            ->rollback(99);
    }

    public function testRollbackCount() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('rollback count must be greater than zero');

        (new MigrationMock(self::$migrationsFile, null))
            ->rollback(0);
    }

    public function testMigrationsFileDoesNotExist() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('migration file does not exist');

        (new MigrationMock(sys_get_temp_dir() . '/phpunit-migrations-not-exist.txt', null))
            ->migrate(1)
            ->rollback(99);
    }

    public function testMigrationsFileInvalid() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('open migrations file');

        (new MigrationMock(sys_get_temp_dir() . '/.', null))
            ->migrate(1)
            ->rollback(99);
    }

    public function testRollbackFileDoesNotExist() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('migration file does not exist');

        (new MigrationMock(sys_get_temp_dir() . '/phpunit-migrations-not-exist.txt', null))
            ->rollback(1);
    }

    public function testNothingToMigrate() : void
    {
        $logger = new Runtime();

        $migration = (new MigrationMock(self::$migrationsFile, $logger))
            ->migrate(null)
            ->migrate(null);

        self::assertSame(0, $migration->count());
        self::assertStringContainsString('INFO migrate - CANCELED - nothing to migrate', implode("\n", $logger->getItems()));
    }

    public function testNothingToRollback() : void
    {
        $logger = new Runtime();

        $migration = (new MigrationMock(self::$migrationsFile, $logger))
            ->rollback(99)
            ->rollback(99);

        self::assertSame(0, $migration->count());
        self::assertStringContainsString('WARNING rollback - CANCELED - nothing to rollback', implode("\n", $logger->getItems()));
    }

    public function testMigrationsFileLock() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('lock migrations file');

        $migration = (new MigrationMock(self::$migrationsFile, null));

        $handle = fopen(self::$migrationsFile, 'r+');

        if (!flock($handle, LOCK_EX)) {
            throw new Exception('lock file');
        }

        try {
            $migration
                ->migrate(null);
        } catch (MigrationException $exception) {
            flock($handle, LOCK_UN);
            fclose($handle);

            throw $exception;
        }
    }

    public function testMigrationsRollbackFileLock() : void
    {
        self::expectException(MigrationException::class);
        self::expectExceptionMessage('lock migrations file');

        $migration = (new MigrationMock(self::$migrationsFile, null));

        $handle = fopen(self::$migrationsFile, 'r+');
        if (!flock($handle, LOCK_EX)) {
            throw new Exception('lock file');
        }

        try {
            $migration
                ->migrate(null)
                ->rollback(1);
        } catch (MigrationException $exception) {
            flock($handle, LOCK_UN);
            fclose($handle);

            throw $exception;
        }
    }

    public function testMigrationFileContainsExtraLines() : void
    {
        file_put_contents(self::$migrationsFile, "\n\n\nup1\nup2\n\n");

        $migration = (new MigrationMock(self::$migrationsFile, null))
            ->migrate(null);

        self::assertSame(3, $migration->count());
    }

    public function testMigrationFileContainsWindowsNewLines() : void
    {
        file_put_contents(self::$migrationsFile, "up1\r\nup2\r\n");

        $migration = (new MigrationMock(self::$migrationsFile, null))
            ->migrate(null);

        self::assertSame(3, $migration->count());
    }
}

class MigrationMock extends AbstractMigration
{
    protected function query(string $sql) : self
    {
        return $this;
    }

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
