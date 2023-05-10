<?php

declare(strict_types=1);

namespace Oct8pus\Migration;

use PDO;
use Psr\Log\LoggerInterface;

abstract class AbstractPDOMigration extends AbstractMigration
{
    protected readonly PDO $db;

    /**
     * Constructor
     *
     * @param string           $file   - migrations file
     * @param string           $host
     * @param string           $user
     * @param string           $pass
     * @param string           $name
     * @param ?LoggerInterface $logger
     */
    public function __construct(string $file, string $host, string $user, string $pass, string $name, ?LoggerInterface $logger = null)
    {
        parent::__construct($file, $logger);

        $this->connect($host, $user, $pass, $name);
    }

    protected function query(string $sql) : self
    {
        $this->db->query($sql);

        return $this;
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
        $this->db = new PDO("mysql:host={$host};dbname={$name};charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $this;
    }
}
