<?php
/**
 * Database connection handler
 */
require_once __DIR__ . '/config.php';

/**
 * Get database connection
 * 
 * @return mysqli Database connection object
 */
function getDbConnection() {
    static $conn;
    
    // If connection already exists, return it
    if ($conn instanceof mysqli) {
        return $conn;
    }
    
    // Create new connection
    $conn = new mysqli(
        'gowork.mysql.database.azure.com', // Azure database host
        'jiaying',                 // Azure database username
        getenv('DB_PASS') ?: 'Mickey1928@', // Password from environment variable or fallback
        'gowork_db',                     // Database name
        3306                             // Port
    );
    
    $conn->ssl_set(NULL, NULL, __DIR__ . '/Users/jiayingsong/Documents/GitHub/GoWork/includes/DigiCertGlobalRootCA.crt.pem', NULL, NULL);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Execute an SQL query with parameters
 * 
 * @param string $sql The SQL query with placeholders
 * @param string $types The types of parameters (i: integer, s: string, d: double, b: blob)
 * @param array $params The parameters to bind to the query
 * @return mysqli_stmt Returns the statement object on success
 * @throws Exception if there's an error executing the query
 */
function executeQuery($sql, $types = '', $params = []) {
    $conn = getDbConnection();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        $error = "Error preparing statement: " . $conn->error;
        error_log($error);
        throw new Exception($error);
    }
    
    if (!empty($params)) {
        $bindParams = [$types];
        
        for ($i = 0; $i < count($params); $i++) {
            $bindParams[] = &$params[$i];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $bindParams);
    }
    
    if (!$stmt->execute()) {
        $error = "Error executing statement: " . $stmt->error;
        error_log($error);
        $stmt->close();
        throw new Exception($error);
    }
    
    return $stmt;
}

/**
 * Fetch a single row from database
 * 
 * @param string $sql The SQL query with placeholders
 * @param string $types The types of parameters
 * @param array $params The parameters to bind
 * @return array|null Returns an associative array of data or null if no rows
 * @throws Exception if there's an error executing the query
 */
function fetchRow($sql, $types = '', $params = []) {
    $stmt = executeQuery($sql, $types, $params);
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

/**
 * Fetch multiple rows from database
 * 
 * @param string $sql The SQL query with placeholders
 * @param string $types The types of parameters
 * @param array $params The parameters to bind
 * @return array Returns an array of associative arrays
 * @throws Exception if there's an error executing the query
 */
function fetchAll($sql, $types = '', $params = []) {
    $stmt = executeQuery($sql, $types, $params);
    
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $stmt->close();
    
    return $rows;
}

/**
 * Insert data into database
 * 
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs
 * @return int Returns the last inserted ID
 * @throws Exception if there's an error executing the query
 */
function insertData($table, $data) {
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    $types = '';
    $values = [];
    
    foreach ($data as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
        $values[] = $value;
    }
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = executeQuery($sql, $types, $values);
    
    $id = getDbConnection()->insert_id;
    $stmt->close();
    
    return $id;
}

/**
 * Update data in database
 * 
 * @param string $table The table name
 * @param array $data Associative array of column => value pairs to update
 * @param string $whereCol The column to use in WHERE clause
 * @param mixed $whereVal The value to use in WHERE clause
 * @return boolean Returns true on success or false if no rows were affected
 * @throws Exception if there's an error executing the query
 */
function updateData($table, $data, $whereCol, $whereVal) {
    $setPart = [];
    $types = '';
    $values = [];
    
    foreach ($data as $column => $value) {
        $setPart[] = "$column = ?";
        
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } else {
            $types .= 'b';
        }
        
        $values[] = $value;
    }
    
    // Add the WHERE value type and value
    if (is_int($whereVal)) {
        $types .= 'i';
    } elseif (is_float($whereVal)) {
        $types .= 'd';
    } elseif (is_string($whereVal)) {
        $types .= 's';
    } else {
        $types .= 'b';
    }
    $values[] = $whereVal;
    
    $sql = "UPDATE $table SET " . implode(', ', $setPart) . " WHERE $whereCol = ?";
    $stmt = executeQuery($sql, $types, $values);
    
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    return $affected > 0;
}

/**
 * Delete data from database
 * 
 * @param string $table The table name
 * @param string $whereCol The column to use in WHERE clause
 * @param mixed $whereVal The value to use in WHERE clause
 * @return boolean Returns true on success or false if no rows were affected
 * @throws Exception if there's an error executing the query
 */
function deleteData($table, $whereCol, $whereVal) {
    $type = is_int($whereVal) ? 'i' : (is_float($whereVal) ? 'd' : 's');
    $sql = "DELETE FROM $table WHERE $whereCol = ?";
    $stmt = executeQuery($sql, $type, [$whereVal]);
    
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    return $affected > 0;
}
