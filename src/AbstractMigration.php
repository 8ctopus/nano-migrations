<?php

declare(strict_types=1);

namespace Oct8pus\Migration;

use Psr\Log\LoggerInterface;

abstract class AbstractMigration
{
    protected readonly ?LoggerInterface $logger;

    private readonly string $file;

    /**
     * Constructor
     *
     * @param string               $file - migration file
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $file, ?LoggerInterface $logger = null)
    {
        $this->file = $file;
        $this->logger = $logger;
    }

    /**
     * Migrate database
     *
     * @param ?int $count
     *
     * @return self
     *
     * @throws MigrationException|PDOException
     */
    public function migrate(?int $count) : self
    {
        if (isset($count) && $count <= 0) {
            throw new MigrationException('migrate count must be > 0');
        }

        if (!file_exists($this->file)) {
            throw new MigrationException('migration file does not exist');
        }

        // get up methods
        $methods = $this->methods('up');

        // get already processed methods
        $migrated = explode("\n", file_get_contents($this->file, false));

        // get not migrated methods
        $methods = array_diff($methods, $migrated);

        // get migrations to migrate
        if ($count) {
            $methods = \array_slice($methods, 0, $count);
        }

        if (\count($methods) === 0) {
            $this->logger?->debug(__FUNCTION__ . ' - CANCELED - nothing to migrate');
            return $this;
        }

        $this->safetyCheck();

        // go through all methods
        foreach ($methods as $method) {
            $this->logger?->info("{$method}...");

            $sql = $this->{$method}();

            $this->query($sql);

            $this->logger?->info("{$method} - OK");

            $migrated[] = $method;

            // save migrated
            $text = implode("\n", $migrated);
            $text = trim($text);

            if (file_put_contents($this->file, $text) === false) {
                throw new MigrationException('save file');
            }
        }
    }

    /**
     * Rollback database
     *
     * @param int $count
     *
     * @return self
     *
     * @throws MigrationException|PDOMigrationException
     */
    public function rollback(int $count) : self
    {
        if ($count <= 0) {
            throw new MigrationException('rollback count must be > 0');
        }

        if (!file_exists($this->file)) {
            throw new MigrationException('migration file does not exist');
        }

        // get all migrations
        $migrated = explode("\n", file_get_contents($this->file, false));

        // remove empty values
        $migrated = array_filter($migrated);

        // get migrations to rollback
        $rollback = \array_slice($migrated, -$count, $count);

        // reverse array to start with last migration
        $rollback = array_reverse($rollback, false);

        // replace up by down
        $methods = str_replace('up', 'down', $rollback);

        if (\count($methods) === 0) {
            $this->logger?->warning(__FUNCTION__ . ' - CANCELED - nothing to rollback');
            return $this;
        }

        $this->safetyCheck();

        // rollback
        foreach ($methods as $method) {
            $this->logger?->info("{$method}...");

            $sql = $this->{$method}();

            $this->query($sql);

            $this->logger?->info("{$method} - OK");

            $migrated = array_values(array_diff($migrated, [str_replace('down', 'up', $method)]));

            // save rollback
            if (file_put_contents($this->file, implode("\n", $migrated)) === false) {
                throw new MigrationException('save rollback');
            }
        }
    }

    /**
     * Run sql query
     *
     * @param  string $sql
     *
     * @return self
     */
    abstract protected function query(string $sql) : self;

    abstract protected function safetyCheck() : self;

    /**
     * Get class methods
     *
     * @param string $filter
     *
     * @return array
     */
    private function methods(string $filter) : array
    {
        $methods = get_class_methods($this);

        return array_filter($methods, function (string $method) use ($filter) {
            if (preg_match("/^{$filter}(\\d{1,2})$/", $method) === 1) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_BOTH);
    }
}
