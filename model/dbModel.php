<?php
require_once __DIR__ . '/../config.php';
// All user values are bound parameters, never joined into SQL.
function query($sql, $types = '', $values = []) {
    global $conn;
    $statement = mysqli_prepare($conn, $sql);
    if ($types !== '') mysqli_stmt_bind_param($statement, $types, ...$values);
    mysqli_stmt_execute($statement);
    return $statement;
}
function rows($sql, $types = '', $values = []) {
    return mysqli_fetch_all(mysqli_stmt_get_result(query($sql, $types, $values)), MYSQLI_ASSOC);
}
function one($sql, $types = '', $values = []) {
    $result = rows($sql, $types, $values);
    return $result[0] ?? null;
}
