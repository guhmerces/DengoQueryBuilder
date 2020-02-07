<?php

namespace Source\Dengo;

use Source\Connection\Connection;

abstract class Builder
{
    /** @var object|null */
    protected $attributes;

    /** @var \PDOException|null */
    protected $fails;

    /** @var string */
    protected $query;

    /** @var string */
    protected $where;

    /** @var string */
    protected $select;

    /** @var string */
    protected $params;

    /** @var string */
    protected $orderBy;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $offset;

    /** @var string $table database table name */
    protected static $table;

    /** @var array $guarded guarded columns */
    protected static $guarded;

    /** @var array $required required columns to be filled */
    protected static $required;

    /**
     * Model constructor.
     * @param string $table database table name
     * @param array $guarded guarded columns
     * @param array $required required columns to be filled
     */
    public function __construct(string $table, array $guarded, array $required)
    {
        self::$table = $table;
        self::$guarded = array_merge($guarded, ['created_at', "updated_at"]);
        self::$required = $required;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->attributes)) {
            $this->attributes = new \stdClass();
        }

        $this->attributes->$name = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->attributes->$name);
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        return ($this->attributes->$name ?? null);
    }

    /**
     * @return null|object
     */
    public function attributes(): ?object
    {
        return $this->attributes;
    }

    /**
     * @return \PDOException
     */
    public function fails(): ?\PDOException
    {
        return $this->fails;
    }

    /*
     * @param string $column
     * @return Builder|mixed
     */
    public function select($column = '*')
    {
        $this->query = "SELECT {$column} FROM ". static::$table ;
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return $this
     */
    public function where(string $column , string $operator, string $value)
    {
        $this->query .= " WHERE {$column} {$operator} :{$column}";
        $this->params[$column] = $value;
        return $this;
    }


    /**
     * @param string $order
     * @return Builder
     */
    public function orderBy(string $order): Builder
    {
        $this->orderBy = " ORDER BY {$order}";
        return $this;
    }

    /**
     * @param int $limit
     * @return Builder
     */
    public function limit(int $limit): Builder
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param int $offset
     * @return Builder
     */
    public function offset(int $offset): Builder
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    /**
     * @param bool $all
     * @return null|array|mixed|Builder
     */
    public function get(bool $all = false)
    {
        try {
            var_dump($this->query . $this->orderBy . $this->limit . $this->offset);
            $smt = Connection::getConnection()->prepare($this->query . $this->orderBy . $this->limit . $this->offset);
            $smt->execute($this->params);

            if(!$smt->rowCount()) {
                return null;
            }

            if ($all) {
                return $smt->fetchAll(\PDO::FETCH_CLASS, static::class);
            }

            return $smt->fetchObject(static::class);

        } catch (\PDOException $e) {
            $this->fails = $e;
            return null;
        }
    }

    /**
     * @param string $key
     * @return int
     */
    public function count(): int
    {
        $smt = Connection::getConnection()->prepare($this->query);
        $smt->execute($this->params);
        return $smt->rowCount();
    }

    /**
     * @param array $attributes
     * @return int|null
     */
    protected function create(array $attributes): ?int
    {
        try {
            $attributes = $this->safe($attributes);
            var_dump($attributes);
            $columns = implode(", ", array_keys($attributes));
            $values = ":" . implode(", :", array_keys($attributes));

            $smt = Connection::getConnection()->prepare("INSERT INTO " . static::$table . " ({$columns}) VALUES ({$values})");
            $smt->execute($this->filter($attributes));

            return Connection::getConnection()->lastInsertId();
        } catch (\PDOException $e) {
            $this->fails = $e;
            return null;
        }
    }

    /**
     * @param array $attributes
     * @param string $terms
     * @param string $params
     * @return int|null
     */
    protected function update(array $attributes, string $terms, string $params): ?int
    {
        try {
            $attributes = $this->safe($attributes);
            $data = [];
            foreach ($attributes as $key => $value) {
                $data[] = "{$key} = :{$key}";
            }
            $data = implode(", ", $data);
            parse_str($params, $params);

            $smt = Connection::getConnection()->prepare("UPDATE " . static::$table . " SET {$data} WHERE {$terms}");
            $smt->execute($this->filter(array_merge($attributes, $params)));

            return ($smt->rowCount() ?? 1);

        } catch (\PDOException $e) {
            $this->fails = $e;
            return null;
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function delete(string $key, string $value): bool
    {
        try {
            $stmt = Connection::getConnection()->prepare("DELETE FROM " . static::$table . " WHERE {$key} = :key");
            $stmt->bindValue("key", $value, \PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            $this->fails = $e;
            return false;
        }
    }

    /**
     * @return array|null
     */
    protected function safe($attributes): ?array
    {
        $safe = $attributes;
        foreach (static::$guarded as $unset) {
            unset($safe[$unset]);
        }
        return $safe;
    }

    /**
     * @param array $attributes
     * @return array|null
     */
    private function filter(array $attributes): ?array
    {
        $filter = [];
        foreach ($attributes as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT));
        }
        return $filter;
    }

    /**
     * @return bool
     */
    protected function required(): bool
    {
        $attributes = (array)$this->attributes();
        foreach (static::$required as $field) {
            if (empty($attributes[$field])) {
                return false;
            }
        }
        return true;
    }
}