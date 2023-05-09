<?php

declare(strict_types=1);

namespace Demo;

use Oct8pus\Migration\AbstractMigration;
use Oct8pus\Migration\MigrationException;
use PDO;
use Psr\Log\LoggerInterface;

final class Migration extends AbstractMigration
{
    private readonly PDO $db;

    /**
     * Constructor
     *
     * @param string               $file
     * @param string               $host
     * @param string               $user
     * @param string               $pass
     * @param string               $name
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $file, string $host, string $user, string $pass, string $name, ?LoggerInterface $logger = null)
    {
        parent::__construct($file, $logger);

        $this->connect($host, $user, $pass, $name);
    }

    public function migrate(?int $count = null) : void
    {
        $this->logger?->debug(__FUNCTION__ . '...');

        parent::migrate($count);

        $this->logger?->notice(__FUNCTION__ . ' - OK');
    }

    public function rollback(int $count) : void
    {
        $this->logger?->debug(__FUNCTION__ . '...');

        parent::rollback($count);

        $this->logger?->notice(__FUNCTION__ . ' - OK');
    }

    protected function query(string $sql) : void
    {
        $this->db->query($sql);
    }

    protected function up1() : string
    {
        return <<<'SQL'
        CREATE TABLE user (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
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

    /**
     * Connect to database
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $name
     *
     * @return self
     *
     * @throws PDOException
     */
    private function connect(string $host, string $user, string $pass, string $name) : self
    {
        if (isset($this->db)) {
            return $this;
        }

        $this->db = new PDO("mysql:host={$host};dbname={$name};charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $this;
    }

    /**
     * Safety check
     *
     * @return void
     *
     * @throws MigrationException
     */
    protected function safetyCheck() : void
    {
        $stdin = fopen('php://stdin', 'r');

        if ($stdin === false) {
            throw new MigrationException('fopen');
        }

        $this->logger?->notice('Confirm action (y/n): ');
        $input = trim(fgets($stdin));

        fclose($stdin);

        if ($input === 'y') {
            return;
        }

        throw new MigrationException('safety check abort');
    }
}
