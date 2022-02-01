<?php

declare(strict_types=1);

namespace Difra\MySQL\Abstracts;

use Difra\Exception;

/**
 * MySQLi Adapter
 * Class MySQLi
 * @package Difra\MySQL
 */
class MySQLi extends Common
{
    /**
     * Check module availability
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('mysqli');
    }

    /**
     * Database connection object
     * @var \mysqli|null
     */
    public ?\mysqli $db = null;

    /**
     * Connect to the database (implementation)
     * @throws Exception
     */
    protected function realConnect()
    {
        $this->db =
            new \mysqli(
                $this->config['hostname'] ?? '',
                $this->config['username'],
                $this->config['password']
            );
        if ($this->db->connect_error) {
            throw new Exception($this->error = $this->db->connect_error);
        }
        $this->db->set_charset('utf8');
        if (!$this->db->select_db($this->config['database'])) {
            throw new Exception($this->error = $this->db->error);
        }
    }

    /**
     * Database query (implementation)
     * @param string $query
     * @throws Exception
     */
    protected function realQuery(string $query): void
    {
        $this->db->query($query);
        if ($err = $this->db->error) {
            throw new Exception("MySQL error: [$err] on request [$query]");
        }
    }

    /**
     * Database fetch (implementation)
     * @param string $query
     * @param bool $replica
     * @return array
     * @throws Exception
     */
    protected function realFetch(string $query, $replica = false): array
    {
        $res = $this->db->query($query);
        if ($err = $this->db->error) {
            throw new Exception('MySQL: ' . $err);
        }
        if ($this->isND()) {
            // mysqlnd is available
            return $res->fetch_all(MYSQLI_ASSOC);
        } else {
            // gather array otherwise
            $table = [];
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $table[] = $row;
            }
        }
        return $table;
    }

    /**
     * Begin transaction
     */
    protected function transactionStart()
    {
        $this->db->autocommit(false);
    }

    /**
     * Commit transaction
     */
    protected function transactionCommit()
    {
        $this->db->autocommit(true);
    }

    /**
     * Cancel transaction
     */
    protected function transactionCancel()
    {
        $this->db->rollback();
        $this->db->autocommit(true);
    }

    /**
     * Escape string
     * @param string $string
     * @return string
     */
    protected function realEscape(string $string): string
    {
        return $this->db->real_escape_string($string);
    }

    /**
     * Call last_insert_id()
     * @return int
     */
    public function getLastId(): int
    {
        return $this->db->insert_id;
    }

    /**
     * Call affected_rows()
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->db->affected_rows;
    }
}
