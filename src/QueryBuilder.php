<?php

namespace App;

use Exception;
use PDO;

class QueryBuilder
{
    private $from;
    private $order = [];
    private $limit;
    private $offset;
    private $where;
    private $select;
    private $params = [];
    private $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }
    public function from(string $table, string $alias = null): self
    {
        $this->from = (!$alias) ? $table : $table . " " . $alias;
        return $this;
    }

    public function orderBy(string $field, string $triDirection): self
    {
        $triDirection = strtoupper($triDirection);
        if (!in_array($triDirection, ['ASC', 'DESC'])) {
            $this->order[] = $field;
        } else {
            $this->order[] = "$field $triDirection";
        }
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $tupleNbr): self
    {
        if (!isset($this->limit)) {
            throw new Exception("Impossible de définir un offset sans définir de limite");
        }
        $this->offset = $tupleNbr;
        return $this;
    }

    public function page(int $nbPage): self
    {
        return $this->offset(($this->limit) * ($nbPage - 1));
    }

    public function where(string $where): self
    {

        $this->where = $where;
        return $this;
    }

    public function setParam(string $field, $value): self
    {
        $this->params[$field] = $value;
        return $this;
    }

    public function select(...$params): self
    {
        if ($this->select) {
            $this->select .= (is_array($params[0])) ? ", " . implode(", ", $params[0]) : ", " . implode(", ", $params);
        } else {
            $this->select =  (is_array($params[0])) ? implode(", ", $params[0]) : implode(", ", $params);
        }
        return $this;
    }

    public function fetch(string $field): ?string
    {
        $query = $this->pdo->prepare($this->toSQL());
        $query->execute($this->params);
        $result = $query->fetch();
        if (!$result) {
            return null;
        }
        return $result[$field];
    }

    public function fetchAll(): array
    {
        try {
            $query = $this->pdo->prepare($this->toSQL());
            $query->execute($this->params);
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Impossible d'éffectuer la requête {$this->toSQL()}: " . $e->getMessage());
        }
    }

    public function count(): int
    {
        /* $query = $pdo->prepare($this->toSQL());
        $query->execute($this->params);
        $result = $query->fetchAll();
        if (!$result) {
            return 0;
        }
        return count($result); */
        // in order to prohibit any modification in our properties we have to use a copy of our current instance. by clonning it.
        return (int) (clone $this)->select("COUNT(id) count")->fetch('count');
    }

    public function toSQL(): string
    {
        $sql = "";
        if (isset($this->select)) {
            $sql .= "SELECT {$this->select}";
        }
        if (isset($this->from)) {
            if (isset($this->select)) {
                $sql .= " FROM {$this->from}";
            } else {
                $sql .= "SELECT * FROM {$this->from}";
            }
        }
        if (isset($this->where)) {
            $sql .= " WHERE {$this->where}";
        }
        if (!empty($this->order)) {
            $sql .= " ORDER BY " . implode(', ', $this->order);
        }
        if ($this->limit > 0) {
            $sql .= " LIMIT {$this->limit}";
        }
        if (isset($this->offset)) {
            $sql .= " OFFSET {$this->offset}";
        }
        return $sql;
    }
}