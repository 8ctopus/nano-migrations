<?php

declare(strict_types=1);

namespace Demo;

use Oct8pus\Migration\AbstractPDOMigration;
use Oct8pus\Migration\MigrationException;

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
