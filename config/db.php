<?php
function dbConnect()
{
    return new mysqli('localhost', 'root', 'root', 'webbylab');

    $mysqli = dbConnect();

    if ($mysqli->connect_errno != 0) {
        die($mysqli->connect_error);
    }
}
