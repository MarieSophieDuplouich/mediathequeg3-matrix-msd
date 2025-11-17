<?php

function get_user_by_email($email)
{
    $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    return db_select_one($query, [$email]);
}


function get_user_by_id($id)
{
    $query = "SELECT * FROM users WHERE id = ? LIMIT 1";
    return db_select_one($query, [$id]);
}


function create_user($name, $email, $password)
{
    $hashed_password = hash_password($password);
    $query = "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
    if (db_execute($query, [$name, $email, $hashed_password])) {
        return db_last_insert_id();
    }
    return false;
}


function update_user_basic($id, $name, $email)
{
    $query = "UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?";
    return db_execute($query, [$name, $email, $id]);
}


function update_user_password($id, $password)
{
    $hashed_password = hash_password($password);
    $query = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    return db_execute($query, [$hashed_password, $id]);
}


function delete_user($id)
{
    $query = "DELETE FROM users WHERE id = ?";
    return db_execute($query, [$id]);
}


function get_all_users($limit = null, $offset = 0)
{
    $query = "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC";
    $params = [];
    if ($limit !== null) {
        $query .= " LIMIT ?, ?";
        $params[] = (int)$offset;
        $params[] = (int)$limit;
        return db_select($query, $params);
    }
    return db_select($query);
}


function count_users()
{
    $result = db_select_one("SELECT COUNT(*) as total FROM users");
    return $result['total'] ?? 0;
}


function email_exists($email, $exclude_id = null)
{
    $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $params = [$email];
    if ($exclude_id) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
    }
    $result = db_select_one($query, $params);
    return $result['count'] > 0;
}