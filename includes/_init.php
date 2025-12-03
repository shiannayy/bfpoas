<?php
require_once "../includes/_config.php";
if ( basename( $_SERVER['PHP_SELF'] ) == basename( __FILE__ ) ) {
    http_response_code( 403 );
    // Forbidden
    die( "403 Forbidden - Direct access not allowed." );
}
/*******************************************/
/* _init.php                               */
/* Central bootstrap for config, DB, auth  */
/*******************************************/

session_start();
$_SESSION['CURRENCY'] = "Php";
$CURRENCY = $_SESSION['CURRENCY'];
/* ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ==
DB CONNECTION
===  ===  ===  ===  ===  ===  ===  ===  ===  ===  == */
//define( "CONN", mysqli_connect( config::DB_HOST, config::DB_USER, DB_PASS, DB_NAME ) );
$CONN = mysqli_connect( config::DB_HOST, config::DB_USER, config::DB_PASS, config::DB_NAME );
if ( !$CONN ) {
    die( "Database connection failed: " . mysqli_connect_error() );
}
$conn = $CONN;

function detect_param_type($value) {
    if (is_int($value)) return 'i';
    if (is_float($value)) return 'd';
    if (is_bool($value)) return 'i';
    return 's';
}
function enforceRoleAccess( $allowedRoles = [] ) {
    // Check if user session exists
    if ( !isset( $_SESSION['user_id'] ) ) {
        header( "Location: ../?login" );
        exit;
    }

    $user = $_SESSION['user_id'];
    $userRole = strtolower( $_SESSION['role'] );

    // If no specific roles were passed, allow all
    if ( empty( $allowedRoles ) ) {
        return;
    }

    // Normalize roles
    $allowedRoles = array_map( 'strtolower', $allowedRoles );

    // Check access
    if ( !in_array( $userRole, $allowedRoles ) ) {
        // Optional: redirect based on user role
        switch ( $userRole ) {
            case 'administrator':
            header( "Location: ../admin/?isloggedin" );
            break;
            case 'client':
            header( "Location: ../client/?isloggedin" );
            break;
            case 'inspector':
            header( "Location: ../inspector/?isloggedin" );
            break;
            default:
            header( "Location: ../?nooneisloggedin" );
        }
        exit;
    }
}
function sanitize_identifier($identifier) {
    if (is_array($identifier)) {
        return array_map('sanitize_identifier', $identifier);
    }
    
    // Allow only alphanumeric, underscores, and dots (for table.column)
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_\.]*$/', $identifier)) {
        error_log("Security: Invalid identifier attempted: $identifier");
        return 'invalid_identifier';
    }
    return $identifier;
}
function sanitize_order_by($order_by) {
    if (!$order_by) return null;
    
    if (is_array($order_by)) {
        $safe_parts = [];
        foreach ($order_by as $col => $dir) {
            $safe_col = sanitize_identifier($col);
            $safe_dir = in_array(strtoupper($dir), ['ASC', 'DESC']) ? strtoupper($dir) : 'ASC';
            $safe_parts[] = "`$safe_col` $safe_dir";
        }
        return implode(", ", $safe_parts);
    }
    
    if (is_string($order_by)) {
        // Parse and validate raw ORDER BY strings
        $parts = explode(',', $order_by);
        $safe_parts = [];
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_\.]*)(?:\s+(ASC|DESC))?$/i', $part, $matches)) {
                $col = sanitize_identifier($matches[1]);
                $dir = isset($matches[2]) ? (in_array(strtoupper($matches[2]), ['ASC', 'DESC']) ? strtoupper($matches[2]) : 'ASC') : 'ASC';
                $safe_parts[] = "`$col` $dir";
            }
        }
        return $safe_parts ? implode(", ", $safe_parts) : null;
    }
    
    return null;
}
function sanitize_limit($limit) {
    if (!$limit) return null;
    
    if (is_array($limit)) {
        // Handle [offset, limit] format
        if (count($limit) === 2 && ctype_digit((string)$limit[0]) && ctype_digit((string)$limit[1])) {
            return (int)$limit[0] . ',' . (int)$limit[1];
        }
        return null;
    }
    
    if (ctype_digit((string)$limit)) {
        return (int)$limit;
    }
    
    // Handle "10, 20" format
    if (preg_match('/^\s*(\d+)\s*(?:,\s*(\d+)\s*)?$/', $limit, $matches)) {
        $offset = isset($matches[2]) ? (int)$matches[1] : 0;
        $limit_val = isset($matches[2]) ? (int)$matches[2] : (int)$matches[1];
        return "$offset, $limit_val";
    }
    
    return null;
}
function sanitize_where_clause($where_string) {
    // For complex raw WHERE clauses, we'll be conservative
    // Only allow simple comparisons with whitelisted operators
    $safe_operators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IS NULL', 'IS NOT NULL'];
    
    // This is a basic sanitizer - for complex queries, consider using array format instead
    $where_string = preg_replace('/[^\w\s=!<>%\.\(\),\-]/', '', $where_string);
    return $where_string;
}  
function select($table, $where = null, $order_by = null, $limit = null, $wherebit = "AND") {
    global $CONN;

    // Sanitize inputs
    $table = sanitize_identifier($table);
    $order_by = sanitize_order_by($order_by);
    $limit = sanitize_limit($limit);

    $query = "SELECT * FROM `$table`";
    $values = [];
    $types = "";

    // ---- WHERE builder ----
    if ($where && is_array($where)) {
        $conditions = [];

        foreach ($where as $column => $value) {
            // Sanitize column name
            $column = sanitize_identifier($column);

            // NOT IN (key format: "!column")
            if (str_starts_with($column, "!") && is_array($value)) {
                $col = substr($column, 1);
                $col = sanitize_identifier($col);
                $placeholders = implode(",", array_fill(0, count($value), "?"));
                $conditions[] = "`$col` NOT IN ($placeholders)";
                foreach ($value as $v) {
                    $values[] = $v;
                    $types .= detect_param_type($v);
                }
            }
            // IN (value is array)
            elseif (is_array($value)) {
                $placeholders = implode(",", array_fill(0, count($value), "?"));
                $conditions[] = "`$column` IN ($placeholders)";
                foreach ($value as $v) {
                    $values[] = $v;
                    $types .= detect_param_type($v);
                }
            }
            // NULL
            elseif (is_null($value)) {
                $conditions[] = "`$column` IS NULL";
            }
            // LIKE (contains %)
            elseif (is_string($value) && strpos($value, '%') !== false) {
                $conditions[] = "`$column` LIKE ?";
                $values[] = $value;
                $types .= 's';
            }
            // Default "="
            else {
                $conditions[] = "`$column` = ?";
                $values[] = $value;
                $types .= detect_param_type($value);
            }
        }

        $query .= " WHERE " . implode(" $wherebit ", $conditions);
    }

    // ---- ORDER BY ----
    if ($order_by) {
        $query .= " ORDER BY " . $order_by;
    }

    // ---- LIMIT ----
    if ($limit) {
        $query .= " LIMIT " . $limit;
    }

    $stmt = mysqli_prepare($CONN, $query);
    if (!$stmt) {
        error_log("SQL Prepare failed: " . mysqli_error($CONN) . " - Query: " . $query);
        return [];
    }

    // ---- Bind parameters ----
    if (!empty($values)) {
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log("SQL Execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    mysqli_stmt_close($stmt);
    return $data;
}
function select_data($table, $where = null, $order_by = null, $limit = null) {
    // Alias for select() with same parameters for backward compatibility
    return select($table, $where, $order_by, $limit);
}
function insert_data($table, $data) {
    global $CONN;
    
    if (empty($data)) {
        error_log("Insert failed: Empty data array");
        return 0;
    }
    
    $table = sanitize_identifier($table);
    
    $columns = [];
    $placeholders = [];
    $values = [];
    $types = '';
    
    foreach ($data as $column => $value) {
        $safe_column = sanitize_identifier($column);
        $columns[] = "`$safe_column`";
        $placeholders[] = "?";
        $values[] = $value;
        $types .= detect_param_type($value);
    }
    
    $columns_str = implode(", ", $columns);
    $placeholders_str = implode(", ", $placeholders);
    
    $query = "INSERT INTO `$table` ($columns_str) VALUES ($placeholders_str)";
    
    $stmt = mysqli_prepare($CONN, $query);
    if (!$stmt) {
        error_log("Insert prepare failed: " . mysqli_error($CONN) . " - Query: " . $query);
        return 0;
    }
    
    if (!empty($values)) {
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Insert execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    // ✅ FIX: Return the last insert ID instead of affected rows
    $insert_id = mysqli_stmt_insert_id($stmt);
    mysqli_stmt_close($stmt);
    
    // Log for debugging
    if ($insert_id > 0) {
        error_log("Insert successful - Table: $table, Insert ID: $insert_id");
    } else {
        error_log("Insert may have failed - Table: $table, Insert ID: $insert_id");
    }
    
    return $insert_id;
}
function update_data($table, $data, $where) {
    global $CONN;
    
    if (empty($data) || empty($where)) {
        error_log("Update failed: Empty data or where clause");
        return 0;
    }
    
    $table = sanitize_identifier($table);
    
    $setParts = [];
    $setValues = [];
    $setTypes = '';
    
    foreach ($data as $key => $val) {
        $safe_key = sanitize_identifier($key);
        $setParts[] = "`$safe_key` = ?";
        $setValues[] = $val;
        $setTypes .= detect_param_type($val);
    }
    $setStr = implode(", ", $setParts);

    $whereParts = [];
    $whereValues = [];
    $whereTypes = '';
    
    foreach ($where as $key => $val) {
        $safe_key = sanitize_identifier($key);
        $whereParts[] = "`$safe_key` = ?";
        $whereValues[] = $val;
        $whereTypes .= detect_param_type($val);
    }
    $whereStr = implode(" AND ", $whereParts);

    $query = "UPDATE `$table` SET $setStr WHERE $whereStr";

    $stmt = mysqli_prepare($CONN, $query);
    if (!$stmt) {
        error_log("Update prepare failed: " . mysqli_error($CONN) . " - Query: " . $query);
        return 0;
    }

    $params = array_merge($setValues, $whereValues);
    $types = $setTypes . $whereTypes;

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Update execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return 0;
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $affected;
}
function delete_data($table, $where) {
    global $CONN;
    
    if (empty($where)) {
        error_log("Delete failed: Empty where clause");
        return 0;
    }
    
    $table = sanitize_identifier($table);
    
    $whereParts = [];
    $whereValues = [];
    $whereTypes = '';
    
    foreach ($where as $key => $val) {
        $safe_key = sanitize_identifier($key);
        $whereParts[] = "`$safe_key` = ?";
        $whereValues[] = $val;
        $whereTypes .= detect_param_type($val);
    }
    $whereStr = implode(" AND ", $whereParts);

    $query = "DELETE FROM `$table` WHERE $whereStr";

    $stmt = mysqli_prepare($CONN, $query);
    if (!$stmt) {
        error_log("Delete prepare failed: " . mysqli_error($CONN));
        return 0;
    }

    mysqli_stmt_bind_param($stmt, $whereTypes, ...$whereValues);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Delete execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return 0;
    }

    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $affected;
}
function select_bit( $table, $where = null, $bitwise = ['AND'], $order_by = null, $limit = null ) {
                global $CONN;
                $query = "SELECT * FROM $table";
                $values = [];

                // WHERE conditions
                if ( $where && is_array( $where ) ) {
                    $conditions = [];
                    $columns = array_keys( $where );
                    $count = count( $columns );

                    // Extend bitwise array if it's shorter than where count - 1
        if (count($bitwise) < $count - 1) {
            $last_op = end($bitwise);
            while (count($bitwise) < $count - 1) {
                $bitwise[] = $last_op;
            }
        }

        // Build conditions
        foreach ($where as $column => $value) {
            if (is_null($value)) {
                $conditions[] = "$column IS NULL";
            } elseif (strpos($value, '%') !== false) {
                $conditions[] = "$column LIKE ?";
                $values[] = $value;
            } else {
                $conditions[] = "$column = ?";
                $values[] = $value;
            }
        }

        // Combine with corresponding bitwise operators
        $where_clause = $conditions[0];
        for ($i = 1; $i < count($conditions); $i++) {
            $operator = isset($bitwise[$i - 1]) ? $bitwise[$i - 1] : 'AND';
            $where_clause .= " $operator " . $conditions[$i];
        }

        $query .= " WHERE $where_clause";
    }

    // ORDER BY
    if ($order_by && is_array($order_by)) {
        $orders = [];
        foreach ($order_by as $column => $direction) {
            $orders[] = "$column $direction";
        }
        $query .= " ORDER BY " . implode(", ", $orders);
    }

    // LIMIT
    if ($limit) {
        $query .= " LIMIT $limit";
    }

    // Prepare and execute
    $stmt = mysqli_prepare($CONN, $query);
    if (!empty($values)) {
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Fetch data
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}
function select_col($table, $columns = ['*'], $where = null, $order_by = null, $limit = null) {
    global $CONN;
    $values = [];

    // Columns
    $col_list = "*";
    if (is_array($columns) && !empty($columns) && $columns !== ['*']) {
        // Sanitize column names (prevent SQL injection through identifiers)
        $safe_cols = array_map(fn($col) => "`" . str_replace("`", "", $col) . "`", $columns);
        $col_list = implode(", ", $safe_cols);
    }

    $query = "SELECT $col_list FROM `$table`";

    // WHERE conditions
    if ($where && is_array($where)) {
        $conditions = [];
        foreach ($where as $column => $value) {
            if (is_null($value)) {
                $conditions[] = "`$column` IS NULL";
            } elseif (strpos($value, '%') !== false) {
                $conditions[] = "`$column` LIKE ?";
                $values[] = $value;
            } else {
                $conditions[] = "`$column` = ?";
                $values[] = $value;
            }
        }
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // ORDER BY
    if ($order_by && is_array($order_by)) {
        $orders = [];
        foreach ($order_by as $column => $direction) {
            $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $orders[] = "`$column` $dir";
        }
        $query .= " ORDER BY " . implode(", ", $orders);
    }

    // LIMIT
    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }

    $stmt = mysqli_prepare($CONN, $query);

    // Bind WHERE values
    if (!empty($values)) {
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $data;
}
function select_aggr(
    string $table,
    array $columns = ["*"],
    array $where = [],
    array $group_by = [],
    array $order_by = [],
    int $limit = null) {
    global $CONN; // ✅ MySQLi connection (from _init.php)

    // SELECT + FROM
    $cols = implode(", ", $columns);
    $sql = "SELECT $cols FROM `$table`";
    $params = [];
    $types = "";

    // WHERE CLAUSE
    if (!empty($where)) {
        $clauses = [];

        foreach ($where as $condition) {
            // Handle simple key-value (no complex condition)
            if (!is_array($condition)) {
                foreach ($where as $key => $value) {
                    $clauses[] = "`$key` = ?";
                    $params[] = $value;
                    $types .= is_numeric($value) ? "i" : "s";
                }
                break;
            }

            $column  = $condition['column'] ?? null;
            $operator = strtoupper(trim($condition['operator'] ?? ' = '));
            $value   = $condition['value'] ?? null;
            $logic   = strtoupper(trim($condition['logic'] ?? 'AND')); // default AND

            if (!$column) continue;

            // IN / NOT IN
            if (in_array($operator, ['IN', 'NOT IN']) && is_array($value)) {
                $placeholders = implode(",", array_fill(0, count($value), "?"));
                $clauses[] = [$logic, "`$column` $operator ($placeholders)"];
                foreach ($value as $v) {
                    $params[] = $v;
                    $types .= is_numeric($v) ? "i" : "s";
                }
            }
            // NULL conditions
            elseif (in_array($operator, ['IS NULL', 'IS NOT NULL'])) {
                $clauses[] = [$logic, "`$column` $operator"];
            }
            // Normal comparison
            else {
                $clauses[] = [$logic, "`$column` $operator ?"];
                $params[] = $value;
                $types .= is_numeric($value) ? "i" : "s";
            }
        }

        if (!empty($clauses)) {
            $sql .= " WHERE ";
            $first = true;
            foreach ($clauses as $clause) {
                if (is_array($clause)) {
                    [$logic, $expr] = $clause;
                    $sql .= $first ? "($expr)" : " $logic ($expr)";
                    $first = false;
                }
            }
        }
    }

    // GROUP BY
    if (!empty($group_by)) {
        $sql .= " GROUP BY " . implode(", ", array_map(fn($c) => "`$c`", $group_by));
    }

    // ORDER BY
    if (!empty($order_by)) {
        $order = [];
        foreach ($order_by as $col => $dir) {
            $dir = strtoupper($dir);
            $order[] = "`$col` " . ($dir === "DESC" ? "DESC" : "ASC");
        }
        $sql .= " ORDER BY " . implode(", ", $order);
    }

    // LIMIT
    if (!empty($limit)) {
        $sql .= " LIMIT " . intval($limit);
    }

    // ✅ Prepare
    $stmt = mysqli_prepare($CONN, $sql);
    if (!$stmt) {
        die("MySQL Prepare Error: " . mysqli_error($CONN));
    }

    // ✅ Bind params (if any)
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    // ✅ Execute
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("MySQL Execution Error: " . mysqli_error($CONN));
    }

    // ✅ Fetch
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $rows;
}
function select_join($tables = [], $columns = ['*'], $joins = [], $where = null, $order_by = null, $limit = null) {
    global $CONN;
    $values = [];

    // Handle main table
    $main_table = !empty($tables) ? $tables[0] : '';

    // Handle columns
    $columns_sql = !empty($columns) ? implode(", ", $columns) : "*";

    $query = "SELECT $columns_sql FROM $main_table";

    // JOINs
    if (!empty($joins) && is_array($joins)) {
        foreach ($joins as $join) {
            $type = strtoupper($join['type'] ?? 'INNER');
            $query .= " $type JOIN {$join['table']} ON {$join['on']}";
        }
    }

    // WHERE
    if ($where) 
    {
        if(is_array($where)){
            $conditions = [];
            foreach ($where as $column => $value) {
                if (is_null($value)) {
                    $conditions[] = "$column IS NULL";
                } elseif (strpos($value, '%') !== false) {
                    $conditions[] = "$column LIKE ?";
                    $values[] = $value;
                } else {
                    $conditions[] = "$column = ?";
                    $values[] = $value;
                }
            }
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
        }else{
            $query .= " WHERE $where";
        }
    }
    

    // ORDER BY
    if ($order_by && is_array($order_by)) {
        $orders = [];
        foreach ($order_by as $column => $direction) {
            $orders[] = "$column $direction";
        }
        $query .= " ORDER BY " . implode(", ", $orders);
    }

    // LIMIT
    if ($limit) {
        $query .= " LIMIT $limit";
    }

    // Prepare & execute
    $stmt = mysqli_prepare($CONN, $query);

    if (!empty($values)) {
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}


function select_join_bit(
    array $tables = [],
    array $columns = ['*'],
    array $joins = [],
    array $where = [],
    array $order_by = [],
    int $limit = null) {
    global $CONN;

    $values = [];
    $types = "";

    // Helper function to properly quote column names
    $quoteColumn = function($col) {
        // If it contains a dot (table.column), quote each part separately
        if (strpos($col, '.') !== false) {
            $parts = explode('.', $col);
            return '`' . implode('`.`', $parts) . '`';
        }
        // If already has backticks or is *, leave it alone
        if (strpos($col, '`') !== false || $col === '*') {
            return $col;
        }
        // Otherwise, wrap in backticks
        return '`' . $col . '`';
    };

    // 🧩 Base SELECT clause
    $main_table = $tables[0] ?? '';
    $columns_sql = implode(", ", $columns);
    $query = "SELECT $columns_sql FROM `$main_table`";

    // 🔗 JOIN handling
    if (!empty($joins)) {
        foreach ($joins as $join) {
            $type  = strtoupper(trim($join['type'] ?? 'INNER'));
            $table = $join['table'] ?? '';
            $on    = $join['on'] ?? '';
            
            if ($table && $on) {
                // Handle table aliases (don't add backticks if there's an alias)
                if (stripos($table, ' as ') !== false) {
                    $query .= " $type JOIN $table ON $on";
                } else {
                    $query .= " $type JOIN `$table` ON $on";
                }
            }
        }
    }

    // 🧠 WHERE clause builder with nested groups
    $buildWhere = function ($conds, $parentLogic = 'AND') use (&$values, &$types, &$buildWhere, $quoteColumn) {
        $parts = [];

        foreach ($conds as $cond) {
            // Nested group
            if (isset($cond['group']) && is_array($cond['group'])) {
                $logic = strtoupper($cond['logic'] ?? 'AND');
                $group_sql = $buildWhere($cond['group'], $logic);
                $parts[] = !empty($parts) ? "$parentLogic ($group_sql)" : "($group_sql)";
                continue;
            }

            // Simple condition
            $column   = $cond['column'] ?? null;
            $operator = strtoupper(trim($cond['operator'] ?? '='));
            $value    = $cond['value'] ?? null;
            $logic    = strtoupper(trim($cond['logic'] ?? 'AND'));

            if (!$column) continue;

            // Properly quote the column name (handles table.column and simple column)
            $quotedColumn = $quoteColumn($column);

            // IN / NOT IN
            if (in_array($operator, ['IN', 'NOT IN']) && is_array($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $expr = "$quotedColumn $operator ($placeholders)";
                foreach ($value as $v) {
                    $values[] = $v;
                    $types .= is_numeric($v) ? 'i' : 's';
                }
            }
            // IS NULL / IS NOT NULL
            elseif (in_array($operator, ['IS NULL', 'IS NOT NULL'])) {
                $expr = "$quotedColumn $operator";
            }
            // Comparison / LIKE
            else {
                $expr = "$quotedColumn $operator ?";
                $values[] = $value;
                $types .= is_numeric($value) ? 'i' : 's';
            }

            // Add logic
            $parts[] = !empty($parts) ? "$logic ($expr)" : "($expr)";
        }

        return implode(' ', $parts);
    };

    if (!empty($where)) {
        $where_sql = $buildWhere($where, 'AND');
        if ($where_sql) {
            $query .= " WHERE $where_sql";
        }
    }

    // 🔢 ORDER BY
    if (!empty($order_by)) {
        $orders = [];
        foreach ($order_by as $col => $dir) {
            $quotedCol = $quoteColumn($col);
            $orders[] = "$quotedCol " . (strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC');
        }
        $query .= " ORDER BY " . implode(", ", $orders);
    }

    // 🔢 LIMIT
    if (!empty($limit)) {
        $query .= " LIMIT " . intval($limit);
    }

    // ✅ Prepare + Bind + Execute
    $stmt = mysqli_prepare($CONN, $query);
    if (!$stmt) {
        error_log("MySQL Prepare Error: " . mysqli_error($CONN) . " - Query: " . $query);
        return [];
    }

    if (!empty($values)) {
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log("MySQL Execute Error: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("MySQL Result Error: " . mysqli_error($CONN));
        mysqli_stmt_close($stmt);
        return [];
    }

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    mysqli_stmt_close($stmt);
    return $data;
}
function select_join2($tables = [], $joins = [], $where = null, $order_by = null, $limit = null) {
    global $CONN;
    $query = "SELECT * FROM $tables[0]";
    $values = [];

    // JOINs
    if (!empty($joins) && is_array($joins)) {
        foreach ($joins as $join) {
            $type = strtoupper($join['type'] ?? 'INNER');
            $query .= " $type JOIN {$join['table']} ON {$join['on']}";
        }
    }

    // WHERE
    if ($where && is_array($where)) {
        $conditions = [];
        foreach ($where as $column => $value) {
            if (is_null($value)) {
                $conditions[] = "$column IS NULL";
            } elseif (strpos($value, '%') !== false) {
                $conditions[] = "$column LIKE ?";
                $values[] = $value;
            } else {
                $conditions[] = "$column = ?";
                $values[] = $value;
            }
        }
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // ORDER BY
    if ($order_by && is_array($order_by)) {
        $orders = [];
        foreach ($order_by as $column => $direction) {
            $orders[] = "$column $direction";
        }
        $query .= " ORDER BY " . implode(", ", $orders);
    }

    // LIMIT
    if ($limit) {
        $query .= " LIMIT $limit";
    }

    $stmt = mysqli_prepare($CONN, $query);

    if (!empty($values)) {
        $types = str_repeat('s', count($values));
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}
function update_data1($table, $data, $where) {
    global $CONN;
    if (!is_array($data) || !is_array($where)) {
        throw new InvalidArgumentException('Both $data and $where must be associative arrays.');
    }

    $set_clause = [];
    foreach ($data as $column => $value) {
        $set_clause[] = "$column = ?";
    }

    $where_clause = [];
    foreach ($where as $column => $value) {
        $where_clause[] = "$column = ?";
    }

    $query = "UPDATE $table SET " . implode(', ', $set_clause) . " WHERE " . implode(' AND ', $where_clause);
    $stmt = mysqli_prepare($CONN, $query);

    $params = array_merge(array_values($data), array_values($where));
    $types = str_repeat('s', count($params));

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    return mysqli_stmt_execute($stmt);
}
function query($sql, $params = []) {
    global $CONN;

    $stmt = mysqli_prepare($CONN, $sql);

    if (!$stmt) {
        throw new Exception("SQL Prepare Error: " . mysqli_error($CONN));
    }

    // If parameters exist, bind them with correct data types
    if (!empty($params)) {

        $types = '';

        foreach ($params as $p) {
            if (is_int($p)) {
                $types .= 'i';  // integer
            } elseif (is_float($p)) {
                $types .= 'd';  // double
            } elseif (is_null($p)) {
                $types .= 's';  // send NULL as string
            } else {
                $types .= 's';  // default string
            }
        }

        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    // For SELECT queries
    if ($result !== false) {
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    // For INSERT, UPDATE, DELETE
    return mysqli_stmt_affected_rows($stmt);
}

function getUserInfo($user_id, $column = "full_name") {
    $user_id = intval($user_id); // Sanitize user_id
    $allowed_columns = ["full_name", "email", "role"]; // Whitelist columns

    if (!in_array($column, $allowed_columns)) {
        return null; // Prevent abuse by only allowing safe columns
    }

    // Use select() utility
    $result = select("users", ["user_id" => $user_id], null, 1);

    if ($result && isset($result[0][$column])) {
        return $result[0][$column];
    }

    return null; // No user or column not found
}
function FSICNo($schedule_id, $inspection_id){
    $schedule = select("inspection_schedule", ["schedule_id" => $schedule_id], null, 1);
    //$inspection = select("inspections", ["schedule_id" => $schedule_id, "inspection_id" => $inspection_id], null, 1);
    
    $sch = $schedule[0];
    $ins = $inspection[0];
    $order_number = $schedule['order_number'] ?? null;
    $last4 = $order_number ? substr($order_number, -4) : '0000';
    
    return FS_CODE . $last4;
    
}
function startInspection($schedule_id, $inspector_id, $user_id) {
    // Get schedule info
    $schedule = select("inspection_schedule", ["schedule_id" => $schedule_id], null, 1);
    if (!$schedule) {
        error_log("ERROR: Schedule not found for schedule_id: $schedule_id");
        return null;
    }

    $schedule = $schedule[0];
    $checklist_id = $schedule['checklist_id'];
    $gen_info_id  = $schedule['gen_info_id'] ?? null;
    $order_number = $schedule['order_number'] ?? null;

    error_log("DEBUG: Found schedule - checklist_id: $checklist_id, gen_info_id: $gen_info_id");

    // Check if inspection already exists
    $existing = select("inspections", ["schedule_id" => $schedule_id], null, 1);
    if ($existing) {
        error_log("DEBUG: Inspection already exists, returning existing");
        return $existing[0];
    }

    // Generate FSIC number
    $last4 = $order_number ? substr($order_number, -4) : '0000';
    $fsic_no = FS_CODE . $last4;
    error_log("DEBUG: Generated FSIC: $fsic_no");

    $data = [
        "gen_info_id"  => $gen_info_id,
        "schedule_id"  => $schedule_id,
        "checklist_id" => $checklist_id,
        "inspector_id" => $inspector_id,
        "started_at"   => date("Y-m-d H:i:s"),
        "status"       => "In Progress",
        "created_by"   => $user_id,
        "fsic_no"      => $fsic_no
    ];

    error_log("DEBUG: Attempting to insert inspection data: " . json_encode($data));

    if (insert_data("inspections", $data)) {
        $new = select("inspections", ["schedule_id" => $schedule_id], null, 1);
        $sched = select("inspection_schedule", ["schedule_id" => $schedule_id], null, 1);
sys_log("Inspection started: for " . $sched[0]['order_number'] . " by Inspector " . $sched[0]['to_officer'], 'ins', $new[0]['inspection_id']);
        return $new ? $new[0] : null;
    } else {
        
        return null;
    }
}
function evaluateInspectionResponse($item, $response_value) {
    // if ($response_value === null || $response_value === "") {
    //     $response_value = "0";
    // }
    switch ($item['checklist_criteria']) {
        case 'textvalue':
            return ($response_value == $item['threshold_text_value']) ? 1 : 0;
        case 'select':
            return ($response_value == $item['threshold_text_value']) ? 1 : 0;
        case 'range':
            return ($response_value >= $item['threshold_range_min'] && $response_value <= $item['threshold_range_max']) ? 1 : 0;

        case 'min_val':
            return ($response_value >= $item['threshold_min_val']) ? 1 : 0;

        case 'max_val':
            return ($response_value <= $item['threshold_max_val']) ? 1 : 0;

        case 'yes_no':
            return ($response_value == $item['threshold_yes_no']) ? 1 : 0;

        case 'days':
            if (!empty($response_value)) {
                try {
                    $response_date = new DateTime($response_value);
                    $today = new DateTime();

                    // Get full signed interval
                    $interval = $today->diff($response_date);
                    $diff_days = (int)$interval->format('%r%a'); // signed difference in days (negative if past)

                    // Example logic:
                    // If diff_days is negative → response date is in the past
                    // If diff_days is positive → response date is in the future
                    // If diff_days == 0 → same day

                    // Evaluate based on threshold
                    // e.g., if still within allowed days (positive & within threshold)
                    if ($diff_days >= 0) {
                        return ($diff_days >= $item['threshold_elapse_day']) ? 1 : 0;
                    } else {
                        // If date already passed (negative), automatically fail
                        return 0;
                    }
                } catch (Exception $e) {
                    // In case the response_value isn’t a valid date
                    return 0;
                }
            }
            return 0;
        
        default:
            return 9; // no criteria → manual evaluation
    }
}
function saveInspectionResponse($schedule_id, $item_id, $response_value = null, $proof_filename = null, $manualpass = false, $notapplicable = false) {

    // if ($response_value === null || $response_value === "") {
    //     $response_value = "0";
    // }
    $na = $notapplicable; 
    $remarks = null;
    $mp = $manualpass;
    // Get the checklist item
    $item = select("checklist_items", ["item_id" => $item_id], null, 1)[0] ?? null;
    if (!$item) return ["success" => false, "remarks" => null, "message" => "Item not found"];

    // Evaluate the response
    


    if($mp === true || $mp === 1 || $mp === '1' || $mp === "1" ){
        $remarks = 1;
    }
    else if($na === true || $na === 1 || $na === '1' || $na === "1"){
        $remarks = 8;
    }
    else{
        $remarks = evaluateInspectionResponse($item, $response_value);
    }
    
    $data = [
        "schedule_id"       => $schedule_id,
        "item_id"           => $item_id,
        "response_value"    => $response_value,
        "remarks"           => $remarks,
        "updated_at"        => date("Y-m-d H:i:s")
    ];

    // If proof filename exists, include it
    if ($proof_filename) {
        $data["response_proof_img"] = $proof_filename;
    }

    // Check if existing record exists
    $existing = select("inspection_responses", [
        "schedule_id" => $schedule_id,
        "item_id"     => $item_id
    ], null, 1);

    if ($existing) {
        $updated = update_data("inspection_responses", $data, [
            "schedule_id" => $schedule_id,
            "item_id"     => $item_id
        ]);
        return ["success" => $updated, "remarks" => $remarks, "manualPass" => $manualpass, "data" => $data];
    } 
    else {
        $inserted = insert_data("inspection_responses", $data);
        return ["success" => $inserted, "remarks" => $remarks, "manualPass" => $manualpass, "data" => $data];
    }
}
function uploadProofImage($schedule_id, $section, $item_id, $file) {
    $targetDir = "../assets/proof/Schedule_{$schedule_id}/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // --- Allowed formats ---
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/jpg'  => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif'
    ];

    // --- Validate MIME type ---
    $mimeType = mime_content_type($file['tmp_name']);
    if (!array_key_exists($mimeType, $allowedTypes)) {
        return ["success" => false, "message" => "Invalid file type: {$mimeType}"];
    }

    // --- Determine file extension ---
    $extension = $allowedTypes[$mimeType];
    $filename = "Proof_{$schedule_id}_{$section}_{$item_id}.{$extension}";
    $targetFile = $targetDir . $filename;

    // --- Replace existing file if any ---
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }

    // --- Move uploaded file ---
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Save filename in database
        update_data("inspection_responses", [
            "response_proof_img" => $filename,
            "updated_at" => date("Y-m-d H:i:s")
        ], [
            "schedule_id" => $schedule_id,
            "item_id" => $item_id
        ]);

        return ["success" => true, "message" => "File uploaded", "filename" => $filename];
    } else {
        return ["success" => false, "message" => "Error moving uploaded file"];
    }
}
function checkFordefects($schedule_id, $checklist_id){
    // 1️⃣ Fetch all checklist responses for this inspection
    $rows = select_join(
        ['inspection_responses'],
        [
            'inspection_responses.response_id',
            'inspection_responses.schedule_id',
            'inspection_responses.item_id',
            'inspection_responses.remarks',
            'checklist_items.item_text',
            'checklist_items.required',
            'CASE
                WHEN checklist_items.checklist_criteria = "range"
                THEN inspection_responses.response_value
                WHEN checklist_items.checklist_criteria = "min_val"
                THEN inspection_responses.response_value
                WHEN checklist_items.checklist_criteria = "max_val"
                THEN inspection_responses.response_value
                WHEN checklist_items.checklist_criteria = "yes_no"
                THEN (
                    CASE
                    WHEN inspection_responses.response_value = "1" THEN "YES"
                    WHEN inspection_responses.response_value = "0" THEN "NO"
                    END
                )
                WHEN checklist_items.checklist_criteria = "days"
                THEN CONCAT(
                    DATEDIFF(
                        DATE( inspection_responses.updated_at ),
                        STR_TO_DATE( inspection_responses.response_value, "%Y-%m-%d" )
                    ),
                    "days [", DATE( inspection_responses.updated_at ), " - ", CAST( inspection_responses.response_value AS DATE ), "]"
                )
                END AS response_value',
            'CASE
                WHEN checklist_items.checklist_criteria = "range"
                THEN CONCAT( checklist_items.threshold_range_min, "-", checklist_items.threshold_range_max )
                WHEN checklist_items.checklist_criteria = "min_val"
                THEN CONCAT( "Must be Greater than or Eq ", checklist_items.threshold_min_val )
                WHEN checklist_items.checklist_criteria = "max_val"
                THEN CONCAT( "Must be less than or equal to ", checklist_items.threshold_max_val )
                WHEN checklist_items.checklist_criteria = "yes_no"
                THEN CONCAT( "Must be ", CASE WHEN checklist_items.threshold_yes_no = 1 THEN "YES" WHEN checklist_items.threshold_yes_no = 0 THEN "NO" END )
                WHEN checklist_items.checklist_criteria = "days"
                THEN CONCAT( "Must be More than or equal to ", checklist_items.threshold_elapse_day, " days" )
                END checklist_criteria'
        ],
        [
            [
                'type' => 'LEFT',
                'table' => 'checklist_items',
                'on' => 'inspection_responses.item_id = checklist_items.item_id'
            ]
        ],
        [
            'inspection_responses.schedule_id' => $schedule_id,
            'checklist_items.checklist_id' => $checklist_id
        ]
    );

    if (empty($rows)) {
        return [
            'score' => 0,
            'issues' => [],
            'message' => 'No inspection responses found.',
            'stats' => [
                'total_items' => 0,
                'passed_items' => 0,
                'failed_items' => 0,
                'not_applicable_items' => 0,
                'required_items' => 0,
                'required_passed' => 0,
                'required_failed' => 0,
                'passed_percentage' => 0,
                'failed_percentage' => 0,
                'not_applicable_percentage' => 0,
                'required_passed_percentage' => 0,
                'required_failed_percentage' => 0,
                'compliance_rate' => 0,
                'has_defects' => false
            ]
        ];
    }

    // 2️⃣ Calculate comprehensive statistics
    $stats = calculateInspectionStats($rows);

    // 3️⃣ Separate passed and failed responses for defect reporting
    $issues = [];
    foreach ($rows as $r) {
        // Only include in issues if it's failed (not passed and not N/A)
        $remarks = isset($r['remarks']) ? (int)$r['remarks'] : 0;
        if ($remarks !== 1 && $remarks !== 8) {
            $issues[] = [
                'item_id' => $r['item_id'],
                'item_text' => $r['item_text'],
                'criteria' => $r['checklist_criteria'],
                'response_value' => $r['response_value'],
                'remarks' => $r['remarks'],
                'required' => $r['required']
            ];
        }
    }

    // 4️⃣ Return structured data with comprehensive stats
    return [
        'score' => $stats['passed_percentage'], // Use passed percentage as score
        'issues' => $issues,
        'total_items' => $stats['total_items'],
        'passed' => $stats['passed_items'],
        'failed' => $stats['failed_items'],
        'has_defects' => $stats['has_defects'],
        'stats' => $stats,
        'message' => $stats['has_defects'] ? 'Inspection completed with defects' : 'Inspection passed successfully'
    ];
}

/**
 * Calculate comprehensive inspection statistics
 */
function calculateInspectionStats($inspectionRows) {
    $stats = [
        'total_items' => count($inspectionRows),
        'passed_items' => 0,
        'failed_items' => 0,
        'not_applicable_items' => 0,
        'required_items' => 0,
        'required_passed' => 0,
        'required_failed' => 0,
        'passed_percentage' => 0,
        'failed_percentage' => 0,
        'not_applicable_percentage' => 0,
        'required_passed_percentage' => 0,
        'required_failed_percentage' => 0,
        'compliance_rate' => 0,
        'has_defects' => false
    ];

    foreach ($inspectionRows as $row) {
        $remarks = isset($row['remarks']) ? (int)$row['remarks'] : 0;
        $required = isset($row['required']) ? (int)$row['required'] : 0;

        // Count passed/failed/not applicable
        if ($remarks === 1) {
            $stats['passed_items']++;
        } elseif ($remarks === 8) {
            $stats['not_applicable_items']++;
        } else {
            $stats['failed_items']++;
        }

        // Count required items and their status
        if ($required === 1) {
            $stats['required_items']++;
            if ($remarks === 1) {
                $stats['required_passed']++;
            } elseif ($remarks !== 8) { // Count as failed if not passed and not N/A
                $stats['required_failed']++;
            }
        }
    }

    // Calculate percentages
    if ($stats['total_items'] > 0) {
        $stats['passed_percentage'] = round(($stats['passed_items'] / $stats['total_items']) * 100, 2);
        $stats['failed_percentage'] = round(($stats['failed_items'] / $stats['total_items']) * 100, 2);
        $stats['not_applicable_percentage'] = round(($stats['not_applicable_items'] / $stats['total_items']) * 100, 2);
    }

    if ($stats['required_items'] > 0) {
        $stats['required_passed_percentage'] = round(($stats['required_passed'] / $stats['required_items']) * 100, 2);
        $stats['required_failed_percentage'] = round(($stats['required_failed'] / $stats['required_items']) * 100, 2);
    }

    // Compliance rate: (Passed Items + N/A Items) / Total Items
    $compliantItems = $stats['passed_items'] + $stats['not_applicable_items'];
    if ($stats['total_items'] > 0) {
        $stats['compliance_rate'] = round(($compliantItems / $stats['total_items']) * 100, 2);
    }

    // Determine if inspection has defects
    $stats['has_defects'] = ($stats['failed_items'] > 0) || ($stats['required_failed'] > 0);

    return $stats;
}


/**
 * Update inspection record with comprehensive statistics
 * Call this after checkFordefects() to save stats to database
 */
function updateInspectionWithStats($schedule_id, $inspectionStats) {
    $updateData = [
        'inspection_score' => $inspectionStats['passed_percentage'],
        'has_Defects' => $inspectionStats['has_defects'] ? 1 : 0,
        'total_items' => $inspectionStats['total_items'],
        'passed_items' => $inspectionStats['passed_items'],
        'failed_items' => $inspectionStats['failed_items'],
        'not_applicable_items' => $inspectionStats['not_applicable_items'],
        'required_items' => $inspectionStats['required_items'],
        'required_passed' => $inspectionStats['required_passed'],
        'required_failed' => $inspectionStats['required_failed'],
        'compliance_rate' => $inspectionStats['compliance_rate'],
        'completed_at' => date('Y-m-d H:i:s'),
        'status' => 'Completed',
        'updated_at' => date('Y-m-d H:i:s')
    ];
    return update_data('inspections', $updateData, ['schedule_id' => $schedule_id]);
}


function completeInspection ($inspection_id, $hasdefects=null, $reference_no=null){
    $existing = select("inspections",
                    ["inspection_id" => $inspection_id],
                     null,
                     1
                    );
    $data = ["completed_at" => date("Y-m-d H:i:s"),
             "has_defects" => $hasdefects,
             "reference_no" => $reference_no
            ];
    $where = ["inspection_id" => $inspection_id ];



    if($existing){
        return update_data("inspections", $data, $where);

        $new = select("inspections", ["inspection_id" => $inspection_id], null, 1);
        $sched = select("inspection_schedule", ["schedule_id" => $new[0]['schedule_id']], null, 1);
         sys_log("Inspection Completed for " . $sched[0]['order_number'] . " by Inspector " . $sched[0]['to_officer'], 'ins', $new[0]['inspection_id']);
    }
    else{
        return 0;
    }
}
function saveResponseSession($schedule_id, $item_id, $response_value, $remarks = null) {
    if (!isset($_SESSION['inspection_responses'][$schedule_id])) {
        $_SESSION['inspection_responses'][$schedule_id] = [];
    }

    // Normalize checkbox values: ensure "0" instead of NULL
    if ($response_value === null || $response_value === "") {
        $response_value = "0";
    }

    $_SESSION['inspection_responses'][$schedule_id][$item_id] = [
        "response_value" => $response_value,
        "remarks" => $remarks
    ];
}
function getInspectionResponses($schedule_id) {
    $rows = select("inspection_responses", ["schedule_id" => $schedule_id]);
    $responses = [];

    foreach ($rows as $row) {
        $responses[$row['item_id']] = [
            "response_id"    => $row['response_id'],
            "response_value" => $row['response_value'],
            "remarks"        => $row['remarks'],
            "response_proof_img" => $row['response_proof_img']
        ];
    }
    return $responses;
}


function handleRecommend($inspection, $role_label, $user_id, $inspection_id) {
    if (!in_array($role_label, ["Recommending Approver"])) {
        return errorResponse("You are not allowed to recommend approval.");
    }

    if ($inspection['hasRecoApproval'] == 1) {
        return errorResponse("Already recommended for approval.");
    }

    $update = update_data("inspections", [
        "hasRecoApproval" => 1,
        "dateRecommended" => date('Y-m-d H:i:s'),
        "recommended_by" => $user_id
    ], ["inspection_id" => $inspection_id]);

    return $update 
        ? ["success" => true, "message" => "Inspection recommended successfully."]
        : errorResponse("Failed to update recommendation.");
}

function handleApprove($inspection, $role_label, $user_id, $inspection_id) {
    if (!in_array($role_label, ["Approver"])) {
        return errorResponse("You are not allowed to approve.");
    }

    if ($inspection['hasFinalApproval'] == 1) {
        return errorResponse("Already approved.");
    }

    $update = update_data("inspections", [
        "hasFinalApproval" => 1,
        "dateApproved" => date('Y-m-d H:i:s'),
        "approved_by" => $user_id
    ], ["inspection_id" => $inspection_id]);

    return $update 
        ? ["success" => true, "message" => "Inspection approved successfully."]
        : errorResponse("Failed to update approval.");
}

function handleReceive($inspection, $role_label, $user_id, $inspection_id) {
    if (!in_array($role_label, ["Client", "Admin_Assistant"])) {
        return errorResponse("You are not allowed to receive certificates.");
    }

    if ($inspection['hasBeenReceived'] == 1) {
        return errorResponse("Certificate already received.");
    }

    $update = update_data("inspections", [
        "hasBeenReceived" => 1,
        "dateReceived" => date('Y-m-d H:i:s'),
        "received_by" => $user_id
    ], ["inspection_id" => $inspection_id]);

    return $update 
        ? ["success" => true, "message" => "Certificate received successfully."]
        : errorResponse("Failed to update receipt.");
}


function getInspection($inspection_id) {
    $inspections = select("inspections", ["inspection_id" => $inspection_id], null, 1);
    return $inspections[0] ?? null;
}

function errorResponse($message) {
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

function successResponse($result) {
    echo json_encode($result);
    exit;
}


function isLoggedIn(): bool {
    global $USER_LOGGED;

    if (!empty($_SESSION['user_id'])) {
        // assign once if not set
        if (!isset($USER_LOGGED)) {
            $USER_LOGGED = $_SESSION['user_id'];
        }
        return true;
    }

    return false;
}
function getRole() {
    return $_SESSION['role'] ?? null;
}
function getSubRole() {
    return $_SESSION['subrole'] ?? null;
}
function isInspector() {
    return getRole() == 'Inspector' || getSubRole() === 'Fire Officer';
}
function isAdmin() {
    return isLoggedIn() && getRole() === 'Administrator';
}
function isClient() {
    return isLoggedIn() && getRole() === 'Client';
}
function isApprover() {
    return isAdmin() && getSubRole() === 'Fire Marshall';
}
function isDataEntry() {
    return isAdmin() && (getSubRole() === 'Data Entry' || getSubRole() === 'Admin_Assistant');
}
function isFireMarshall() {
    return getSubRole() === 'Fire Marshall';
}
function isRecoApprover() {
    return isChiefFSES();
}
function isChiefFSES() {
    return isAdmin() && getSubRole() === 'Chief FSES';
}
function requireRole($roles = [], $sub_roles = []) {
    if (!isLoggedIn()) {
        header("Location: /public/index.php?unauthorized=1");
        exit;
    }

    $user_role = getRole();
    $user_sub  = getSubRole();

    if (!empty($roles) && !in_array($user_role, $roles)) {
        header("Location: /public/index.php?unauthorized=1");
        exit;
    }

    if (!empty($sub_roles) && (!isAdmin() || !in_array($user_sub, $sub_roles))) {
        header("Location: /public/index.php?unauthorized=1");
        exit;
    }
}
function esignature($user_id) {
    $result = select("users", ["user_id" => $user_id], null, 1);

    if ($result && isset($result[0]['signature'])) {
        return $result[0]['signature'];
    }
    return null; // schedule_id not found
}
function isSignedBy($role, $id, $doc_type=1) {
    if($doc_type == 1){
        $schedule_id = $id;
        $result = select("inspection_schedule", ["schedule_id" => $schedule_id], null, 1);

        if (!$result) {
            return false; // no schedule found
        }

        $row = $result[0];

        // Map roles to their corresponding DB column
        $roleMap = [
            'Client'                 => 'HasClientAck',
            'Recommending Approver'  => 'hasRecommendingApproval',
            'Final Approver'         => 'hasFinalApproval',
            'Inspector'              => 'hasInspectorAck'
        ];
        
        
        if (!isset($roleMap[$role])) {
            return false; 
        }
    }
    else if ( $doc_type == 2){
        $inspection_id = $id;
        $result = select("inspections", ["inspection_id" => $inspection_id], null, 1);

        if (!$result) {
            return false; // no schedule found
        }

        $row = $result[0];

        // Map roles to their corresponding DB column
        $roleMap = [
            'Client'                 => 'hasBeenReceived',
            'Recommending Approver'  => 'hasRecoApproval',
            'Approver'         => 'hasFinalApproval'
        ];
        
        
        if (!isset($roleMap[$role])) {
            return false; 
        }
    }

    $column = $roleMap[$role];

    return !empty($row[$column]) && (strtoupper($row[$column]) === 'Y' || $row[$column] === 1);
}
function acknowledgeSchedule($schedule_id, $user_id, $role) {
    
    $now = date("Y-m-d H:i:s");
    $data = [
        "updated_at" => $now
    ];

    switch ($role) {
        case 'Client':
            $data += [
                "HasClientAck" => 'Y',
                "AckByClient_id" => $user_id,
                "DateAckbyClient" => $now
            ];
            break;
        case 'Recommending Approver':
            $data += [
                "hasRecommendingApproval" => 1,
                "RecommendingApprover" => $user_id,
                "dateRecommendedForApproval" => $now
            ];
            break;

        case 'Approver':
            $data += [
                "hasFinalApproval" => 1,
                "FinalApprover" => $user_id,
                "dateFinalApproval" => $now
            ];
            break;
        case 'Inspector':
            $data += [
                "hasInspectorAck" => 1,
                "dateInspectorAck" => $now,
                "inspector_id" => $user_id
            ];
            break;

        default:
            return false; // invalid role
    }
    $user_fn = getUserInfo($user_id);
    
    
    /*log*/
    $order_number = getSchedInfo($schedule_id)['order_number'];
    $msg = $user_fn . "(".$role.") " . " has acknowledged " . $order_number . " on " . $now; 
    sys_log($msg,'sch',$schedule_id);
    /*end log*/
    
    $current_remarks = select("inspection_schedule",['schedule_id' => $schedule_id])[0]['remarks'];
    $current_remarks = "[" . $role ."] Has Acknowledged. <br>";    
    
    if($role === 'Approver'){
            $current_remarks .= "Inspection may now start on Scheduled date.";
    }
    $data += ["remarks" => $current_remarks ];
    return update_data("inspection_schedule", $data, ["schedule_id" => $schedule_id],);
    
    
}
function markScheduleUnseen( $schedule_id, $rolesToNotify = [], $skipRole = null ) {
        global $CONN;
                        // or your DB wrapper
                        $updates = [];
                        foreach ( $rolesToNotify as $roleKey => $col ) {
                            if ( $roleKey === $skipRole ) {
                                // set seen for acting role
                                $updates[$col] = 1;
                            } else {
                                $updates[$col] = 0;
                            }
                        }
                        $updates['updated_at'] = date( 'Y-m-d H:i:s' );
                        return update_data( 'inspection_schedule', $updates,
                        ['schedule_id' => $schedule_id] );
                    }

                    function sys_log( $msg, $action = null, $val = null ) {
                        $table = "logs";
                        $col = "user_id";
                        $now = date( "Y-m-d H:i:s" );
                        if ( !empty( $action ) && !empty( $val ) ) {
                            switch( $action ) {
                                case "sch": $col = "schedule_id";
                                break;
                                case "ins": $col = "inspection_id";
                                break;
                                case "chk": $col = "checklist_id";
                                break;
                                case "login": $col = "user_id";
                                break;
                                default: $col = "user_id";
                            }
                        }
                        $columns = [
                            "log_action" => $action,
                            "log_message" => $msg,
                            $col => $val
                        ];

                        return insert_data( $table, $columns );
                    }

                    /**
                    * Logout user and destroy session
                    * Usage:
                    *   user_logout();
                    */

                    function user_logout() {
                        $_SESSION = [];
                        if ( ini_get( "session.use_cookies" ) ) {
                            $params = session_get_cookie_params();
                            setcookie( session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    session_destroy();
                }

                /*Alerts*/

                function alert( $str, $type = "danger" ) {
                    echo "<div class='alert alert-sm alert-". $type ."'>".$str ."</div>";
                }

                /*String Cleaner*/

                function e( $str ) {
                    return htmlspecialchars( ( string )$str, ENT_QUOTES, 'UTF-8' );
                }

                function getConfigValue( $key ) {
                    global $CONN;

                    // Sanitize key to prevent SQL injection
                    $safe_key = mysqli_real_escape_string( $CONN, $key );

                    // Fetch record using your select_data() helper
                    $rows = select_data( 'system_config', "config_key = '{$safe_key}'" );

                    if ( empty( $rows ) ) return null;

                    $row = $rows[0];

                    $encrypted_value = $row['config_value'];
                    $iv = base64_decode( $row['iv_value'] );

                    // Use a constant or config variable instead of hardcoding
                    $secret = config::APP_SECRET;

                    // Decrypt value
                    $decrypted_value = openssl_decrypt( $encrypted_value, 'AES-128-CTR', $secret, 0, $iv );

                    return $decrypted_value ?: null;
                }

                function getSchedInfo( $sched_id ) {
                    global $CONN;

                    $data = select( "inspection_schedule", ["schedule_id" => $sched_id], null, 1 );

                    if ( !empty( $data ) ) {
                        return $data[0];

                    } else {
                        return null;
                    }

                }

                function getGenInfo( $gen_info_id ) {
                    global $CONN;

                    $gen_info_id = intval( $gen_info_id );
                    if ( $gen_info_id <= 0 ) {
                        return null;
                        // Invalid ID
                    }

                    // Build the join query using your helper
                    $result = select_join(
                        ['general_info'], // main table
                        [
                            'general_info.*',
                            'map_saved_location.address AS location_address',
                            'map_saved_location.lat AS location_lat',
                            'map_saved_location.lng AS location_lng'
                        ],
                        [
                            [
                                'type' => 'LEFT',
                                'table' => 'map_saved_location',
                                'on' => 'general_info.loc_id = map_saved_location.loc_id'
                            ]
                        ],
                        ['general_info.gen_info_id' => $gen_info_id],
                        null,
                        1
                    );

                    return !empty( $result ) ? $result[0] : null;
}
function getFSEDCode($checklist_id){
    
        $res = select("checklists",["checklist_id" => $checklist_id],null,1);
    if(!empty($res)){
        return $res[0]['fsed_code'];
    }
    else{
        return "";
    }
    
}              
function getIcon( $type, $size = ["16", "16","px"]) {
    $w = $size[0];
    $h = $size[1];
    $p = $size[2];

        switch( $type ) {
            case "attach":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-paperclip' viewBox='0 0 16 16'>
              <path d='M4.5 3a2.5 2.5 0 0 1 5 0v9a1.5 1.5 0 0 1-3 0V5a.5.5 0 0 1 1 0v7a.5.5 0 0 0 1 0V3a1.5 1.5 0 1 0-3 0v9a2.5 2.5 0 0 0 5 0V5a.5.5 0 0 1 1 0v7a3.5 3.5 0 1 1-7 0z'/>
            </svg>";
            break;
            case "expand":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-chevron-bar-expand' viewBox='0 0 16 16'>
                <path fill-rule='evenodd' d='M3.646 10.146a.5.5 0 0 1 .708 0L8 13.793l3.646-3.647a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 0-.708m0-4.292a.5.5 0 0 0 .708 0L8 2.207l3.646 3.647a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 0 0 0 .708M1 8a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13A.5.5 0 0 1 1 8'/>
            </svg>";
            break;
            case "checklist":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-ui-checks' viewBox='0 0 16 16'>
              <path d='M7 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zM2 1a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm0 8a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2zm.854-3.646a.5.5 0 0 1-.708 0l-1-1a.5.5 0 1 1 .708-.708l.646.647 1.646-1.647a.5.5 0 1 1 .708.708zm0 8a.5.5 0 0 1-.708 0l-1-1a.5.5 0 0 1 .708-.708l.646.647 1.646-1.647a.5.5 0 0 1 .708.708zM7 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 8a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5'/>
            </svg>";
            break;
            case "patchcheck":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-patch-check-fill' viewBox='0 0 16 16'>
              <path d='M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708'/>
            </svg>";
            break;
            case "patchcaution":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-patch-exclamation-fill' viewBox='0 0 16 16'>
              <path d='M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2'/>
            </svg>";
            break;
            case "geo":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-geo' viewBox='0 0 16 16'>
                <path fill-rule='evenodd' d='M8 1a3 3 0 1 0 0 6 3 3 0 0 0 0-6M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999zm2.493 8.574a.5.5 0 0 1-.411.575c-.712.118-1.28.295-1.655.493a1.3 1.3 0 0 0-.37.265.3.3 0 0 0-.057.09V14l.002.008.016.033a.6.6 0 0 0 .145.15c.165.13.435.27.813.395.751.25 1.82.414 3.024.414s2.273-.163 3.024-.414c.378-.126.648-.265.813-.395a.6.6 0 0 0 .146-.15l.015-.033L12 14v-.004a.3.3 0 0 0-.057-.09 1.3 1.3 0 0 0-.37-.264c-.376-.198-.943-.375-1.655-.493a.5.5 0 1 1 .164-.986c.77.127 1.452.328 1.957.594C12.5 13 13 13.4 13 14c0 .426-.26.752-.544.977-.29.228-.68.413-1.116.558-.878.293-2.059.465-3.34.465s-2.462-.172-3.34-.465c-.436-.145-.826-.33-1.116-.558C3.26 14.752 3 14.426 3 14c0-.599.5-1 .961-1.243.505-.266 1.187-.467 1.957-.594a.5.5 0 0 1 .575.411'/>
            </svg>";
            break;
            case "card-checklist":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-card-checklist' viewBox='0 0 16 16'>
              <path d='M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z'/>
              <path d='M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0'/>
            </svg>";
            break;
            case "pdf":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-filetype-pdf' viewBox='0 0 16 16'>
              <path fill-rule='evenodd' d='M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z'/>
            </svg>";
            break;
            case "user":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-person-square' viewBox='0 0 16 16'>
                             <path d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0' />
                             <path d='M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1v-1c0-1-1-4-6-4s-6 3-6 4v1a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z' />
                         </svg>";
            break;
            case "new_user":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-person-fill-add' viewBox='0 0 16 16'>
                      <path d='M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0'/>
                      <path d='M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4'/>
                    </svg>";
            break;
            case "building":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-building-fill-gear' viewBox='0 0 16 16'>
                             <path d='M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v7.256A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-3.59 1.787A.5.5 0 0 0 9 9.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .39-.187A4.5 4.5 0 0 0 8.027 12H6.5a.5.5 0 0 0-.5.5V16H3a1 1 0 0 1-1-1zm2 1.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3 0v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5M7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5M4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z' />
                             <path d='M11.886 9.46c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0' />
                         </svg>";
            break;
            case "newbuilding":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-building-fill-add' viewBox='0 0 16 16'>
                             <path d='M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0' />
                             <path d='M2 1a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v7.256A4.5 4.5 0 0 0 12.5 8a4.5 4.5 0 0 0-3.59 1.787A.5.5 0 0 0 9 9.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .39-.187A4.5 4.5 0 0 0 8.027 12H6.5a.5.5 0 0 0-.5.5V16H3a1 1 0 0 1-1-1zm2 1.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3 0v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5m3.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zM4 5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5M7.5 5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm2.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5M4.5 8a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z' />
                         </svg>";
            break;
            case "calendar":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-calendar4-week' viewBox='0 0 16 16'>
                             <path d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z' />
                             <path d='M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-2 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z' />
                         </svg>";
            break;
            case "addtocal":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-calendar-plus' viewBox='0 0 16 16'>
                             <path d='M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7' />
                             <path d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z' />
                         </svg>";
            break;
            case "inslist":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-list-check' viewBox='0 0 16 16'>
                             <path fill-rule='evenodd' d='M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M3.854 2.146a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 3.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708L2 7.293l1.146-1.147a.5.5 0 0 1 .708 0m0 4a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0' />
                         </svg>";
            break;
            case "award":
            return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-award' viewBox='0 0 16 16'>
                      <path d='M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z'/>
                      <path d='M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z'/>
                    </svg>";
            break;
            case "newsched":
                return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-file-earmark-plus' viewBox='0 0 16 16'>
                          <path d='M8 6.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V11a.5.5 0 0 1-1 0V9.5H6a.5.5 0 0 1 0-1h1.5V7a.5.5 0 0 1 .5-.5'/>
                          <path d='M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z'/>
                        </svg>"; break;
            case "menudots":
                return "<svg xmlns='http://www.w3.org/2000/svg' width='{$w}{$p}' height='${h}{$p}' fill='currentColor' class='bi bi-three-dots-vertical' viewBox='0 0 16 16'>
                          <path d='M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0'/>
                        </svg>"; break;
                
                
            case "caretup" : 
                return "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-up-fill' viewBox='0 0 16 16'>
                            <path d='m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z'/>
                        </svg>"; break;
            case "caretdown": 
                return "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-down-fill' viewBox='0 0 16 16'>
                            <path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/>
                        </svg>";
                break;
            case "logout":
                return "<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-door-open' viewBox='0 0 16 16'>
                            <path d='M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1'/>
                            <path d='M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117M11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5M4 1.934V15h6V1.077z'/>
                        </svg>";
                break;
            default: return;
        }
}
function randomNDigits( $n ) {
                    if ( $n <= 0 ) {
                        return 0;
                        // invalid input
                    }

                    // Smallest number with n digits ( e.g., 1000 for n = 4 )
                    $min = pow( 10, $n - 1 );

                    // Largest number with n digits ( e.g., 9999 for n = 4 )
                    $max = pow( 10, $n ) - 1;

                    return mt_rand( $min, $max );
                }

function getRoleLabel ( $mainrole, $subrole ) {
    if ( $mainrole === "Administrator" && in_array( $subrole, ["Chief FSES"] ) ) {
        return "Recommending Approver";
    }
    if ( $mainrole === "Administrator" && in_array( $subrole, ["Fire Marshall"] ) ) {
        return "Approver";
    } else if ( $mainrole === "Administrator" && $subrole == "Admin_Assistant" ) {
        return "Admin_Assistant";
    } else if ( $mainrole === "Inspector") {
        return "Inspector";
    } else if ( $mainrole === "Client" ) {
        return "Client";
    } else {
        return "Guest";
    }
}

                $pages = [
                    'checklist' => ["label" => "CHECKLISTS", "link" => "?page=view_checklists", "section" => "config", "icon" => "checklist"],
                    'establishment' => ["label" => "ESTABLISHMENTS", "link" => "?page=est_list", "section" => "establishment", "icon" => "building"],
                    'new_est' => ["label" => "NEW ESTABLISHMENT", "link" => "?page=new_est", "section" => "establishment", "icon" => "newbuilding"],
                    'sched_ins' => ["label" => "SCHEDULE INSPECTION", "link" => "?page=sched_ins", "section" => "inspections", "icon" => "newsched"],
                    'inspection_sched' => ["label" => "INSPECTION SCHEDULE", "link" => "?page=ins_sched", "section" => "inspections", "icon" => "calendar"],
                    'inspection_list' => ["label" => "INSPECTIONS CERTIFICATE", "link" => "?page=ins_list", "section" => "inspections", "icon" => "award"],
                    'user_list' => ["label" => "USERS", "link" => "?page=user_list", "section" => "users", "icon" => "user"],
                    'new_user' => ["label" => "ADD USERS", "link" => "?page=new_user", "section" => "users", "icon" => "new_user"]
                ];
                $roleButtons = [
                    "Recommending Approver" => [
                        $pages['establishment'], $pages['inspection_sched'], $pages['inspection_list']
                    ],
                    "Approver" => [
                        $pages['establishment'], $pages['inspection_sched'], $pages['inspection_list']
                    ],
                    "Admin_Assistant" => [
                        $pages['user_list']
                        , $pages['new_user']
                        , $pages['sched_ins']
                        , $pages['checklist'], $pages['establishment']
                        , $pages['new_est'], $pages['inspection_sched']
                        , $pages['inspection_list']
                    ],
                    "Inspector" => [
                        $pages['establishment'], $pages['inspection_sched'], $pages['inspection_list']
                    ],
                    "Client" => [
                         $pages['establishment'], $pages['inspection_sched'], $pages['inspection_list']
                    ]
                ];

function appNavBtn( $linkHref = "#", $icon = "menu", $label = NULL,  $externalElementClass = "col-lg-3 col-6" ) {
                    return '
        <div class="' . htmlspecialchars( $externalElementClass ) . '">
            <a href="' . htmlspecialchars( $linkHref ) . '" class="page-btn btn btn-gold text-navy mb-2 w-100">
                <div class="mt-3 text-navy">'
                    . getIcon( $icon, [20,20,"%"] ) .
                    '</div>
                <div class="small">
                    
                    <span class="small text-small text-uppercase">' . htmlspecialchars( $label ) . '</span>
                </div>
            </a>
        </div>';
}



function navBarBtn ($linkhref = '#', $icon = 'menu', $label = NULL) {
    return ' <li class="nav-item">
                    <a class="nav-link" href="' . $linkhref .'" data-link="home">' .  getIcon($icon, [20,20,'px']) . ' <small class="d-lg-none d-md-none d-inline">' . $label .'</small>' .'</a>
            </li>';
}

function encrypt_id($id) {
    $key = "bfpoas_enc"; 
    $cipher = "AES-128-CTR";
    $iv = substr(hash('sha256', $key), 0, 16);

    return base64_encode(openssl_encrypt($id, $cipher, $key, 0, $iv));
}
function decrypt_id($encrypted) {
    $key = "bfpoas_enc";
    $cipher = "AES-128-CTR";
    $iv = substr(hash('sha256', $key), 0, 16);

    return openssl_decrypt(base64_decode($encrypted), $cipher, $key, 0, $iv);
}

