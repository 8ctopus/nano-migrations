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
     * @param PDO              $db
     * @param ?LoggerInterface $logger
     */
    public function __construct(string $file, PDO $db, ?LoggerInterface $logger = null)
    {
        parent::__construct($file, $logger);

        $this->db = $db;
    }

    /**
     * Run sql query
     *
     * @param  string $sql
     *
     * @return self
     */
    protected function query(string $sql) : self
    {
        $this->db->query($sql);

        return $this;
    }
}
