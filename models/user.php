<?php

class User
{
    public static function emailExists($email)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function getUserByEmail($email)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT id, email, password FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function addUser($username, $password_hash, $email, $phone)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password_hash, $email, $phone);
        $stmt->execute();
    }


}
