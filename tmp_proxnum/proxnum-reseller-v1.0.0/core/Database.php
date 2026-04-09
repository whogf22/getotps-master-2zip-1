<?php
/**
 * Core Database Class
 * Handles all database operations
 */

namespace Core;

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Set MySQL timezone to match PHP timezone to prevent timestamp inconsistencies
            $phpTimezone = date_default_timezone_get();
            $this->pdo->exec("SET time_zone = '+00:00'"); // Use UTC
        } catch (\PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Execute query
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new \Exception('Database query failed');
        }
    }
    
    // Fetch all rows
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    // Fetch single row
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    // Insert and return ID
    public function insert($table, $data) {
        $keys = array_keys($data);
        // Escape column names with backticks to handle reserved words
        $fields = implode(', ', array_map(function($key) { return "`$key`"; }, $keys));
        $placeholders = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
        $this->query($sql, $data);
        
        return $this->pdo->lastInsertId();
    }
    
    // Update
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        $params = [];
        $i = 0;
        
        // Build SET clause with positional parameters and escaped column names
        foreach ($data as $key => $value) {
            $set[] = "`$key` = ?";
            $params[] = $value;
        }
        $setClause = implode(', ', $set);
        
        // Merge with WHERE parameters
        $params = array_merge($params, $whereParams);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        return $this->query($sql, $params);
    }
    
    // Delete
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql, $params);
    }
    
    // Begin transaction
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    // Commit transaction
    public function commit() {
        return $this->pdo->commit();
    }
    
    // Rollback transaction
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    // Get row count
    public function count($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $result = $this->fetch($sql, $params);
        return (int)$result['count'];
    }
}
