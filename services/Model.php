<?php

namespace SLSupplyHub;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $validationRules = [];
    protected $transactionCount = 0;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find($id)
    {
        try {
            // Add explicit cast to handle auto-increment IDs properly
            $id = (int)$id;
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $stmt = $this->db->executeQuery($sql, [$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[Model] Find error: " . $e->getMessage());
            return null;
        }
    }

    public function all()
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->db->executeQuery($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[Model] All error: " . $e->getMessage());
            return [];
        }
    }

    protected function create($data)
    {
        try {
            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';

            $sql = "INSERT INTO {$this->table} (" . implode(',', $fields) . ") 
                    VALUES ($placeholders)";

            error_log("[Model] Create SQL: " . $sql);
            error_log("[Model] Create data: " . json_encode($data));

            $lastId = $this->db->executeQuery($sql, $values);

            if (!$lastId) {
                throw new \Exception("Insert failed - no ID generated");
            }

            error_log("[Model] Successfully created record with ID: " . $lastId);
            return $lastId;
        } catch (\Exception $e) {
            error_log("[Model] Create error: " . $e->getMessage());
            if ($e->getPrevious()) {
                error_log("[Model] Previous error: " . $e->getPrevious()->getMessage());
            }
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            throw new \Exception("No fillable data provided");
        }

        $fields = array_map(function ($field) {
            return "$field = ?";
        }, array_keys($data));

        $sql = "UPDATE {$this->table} SET " . implode(',', $fields) . " WHERE {$this->primaryKey} = ?";

        $values = array_values($data);
        $values[] = $id;

        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function where($conditions, $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE $conditions";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function paginate($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;

        // Get total count
        $countStmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM {$this->table}");
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Get paginated results
        $sql = "SELECT * FROM {$this->table} LIMIT ? OFFSET ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$perPage, $offset]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'items' => $items,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }

    protected function filterFillable(array $data)
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function validate(array $data)
    {
        $errors = [];

        foreach ($this->validationRules as $field => $rules) {
            if (!isset($data[$field])) {
                if (in_array('required', $rules)) {
                    $errors[$field][] = "The $field field is required.";
                }
                continue;
            }

            $value = $data[$field];
            foreach ($rules as $rule) {
                if (is_callable($rule)) {
                    $result = $rule($value);
                    if ($result !== true) {
                        $errors[$field][] = $result;
                    }
                    continue;
                }

                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "The $field field is required.";
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "The $field must be a valid email address.";
                        }
                        break;

                    case 'numeric':
                        if (!is_numeric($value)) {
                            $errors[$field][] = "The $field must be a number.";
                        }
                        break;

                    default:
                        if (preg_match('/min:(\d+)/', $rule, $matches)) {
                            $min = $matches[1];
                            if (strlen($value) < $min) {
                                $errors[$field][] = "The $field must be at least $min characters.";
                            }
                        } elseif (preg_match('/max:(\d+)/', $rule, $matches)) {
                            $max = $matches[1];
                            if (strlen($value) > $max) {
                                $errors[$field][] = "The $field may not be greater than $max characters.";
                            }
                        }
                }
            }
        }

        return $errors;
    }

    public function beginTransaction()
    {
        if ($this->transactionCount == 0) {
            $this->db->getConnection()->beginTransaction();
        }
        $this->transactionCount++;
    }

    public function commit()
    {
        if ($this->transactionCount == 1) {
            $this->db->getConnection()->commit();
        }
        $this->transactionCount--;
    }

    public function rollback()
    {
        if ($this->transactionCount == 1) {
            $this->db->getConnection()->rollBack();
        }
        $this->transactionCount--;
    }

    public function inTransaction()
    {
        return $this->db->getConnection()->inTransaction();
    }

    public function raw($query, $params = [])
    {
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}
