<?php

class Actor
{
    public static function getListAllActors()
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT * FROM actors");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function checkActorExist($firstName, $lastName, $excludeActorId = null)
    {
        $mysqli = dbConnect();

        $exist = false;
        $sql = "SELECT id FROM actors WHERE first_name = ? AND last_name = ?";

        if ($excludeActorId !== null) {
            $sql .= " AND id != ?";
        }

        $stmt_check = $mysqli->prepare($sql);

        if ($excludeActorId !== null) {
            $stmt_check->bind_param("ssi", $firstName, $lastName, $excludeActorId);
        } else {
            $stmt_check->bind_param("ss", $firstName, $lastName);
        }

        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $exist = true;
        }
        return $exist;
    }

    public static function getOrCreateActor($actorFirstName, $actorLastName)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT id FROM actors WHERE first_name = ? AND last_name = ?");
        $stmt->bind_param("ss", $actorFirstName, $actorLastName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['id'];
        }

        $stmt = $mysqli->prepare("INSERT INTO actors (first_name, last_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $actorFirstName, $actorLastName);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public static function insertActorsMovies($actorId, $movieId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("INSERT INTO actor_movie (actor_id, movie_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $actorId, $movieId);
        $stmt->execute();
    }

    public static function addActor($firstName, $lastName)
    {
        $mysqli = dbConnect();
        if (self::checkActorExist($firstName, $lastName)) {
            return false;
        }
        $stmt_check = $mysqli->prepare("SELECT id FROM actors WHERE first_name = ? AND last_name = ?");
        $stmt_check->bind_param("ss", $firstName, $lastName);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            return false;
        }

        $stmt_insert = $mysqli->prepare("INSERT INTO actors (first_name, last_name) VALUES (?, ?)");
        $stmt_insert->bind_param("ss", $firstName, $lastName);
        $stmt_insert->execute();

        return $stmt_insert->insert_id;
    }

    public static function getAllActors($page = 1, $limit = 10)
    {
        $mysqli = dbConnect();
        $offset = ($page - 1) * $limit;

        $stmt = $mysqli->prepare("SELECT * FROM actors ORDER BY first_name COLLATE utf8mb4_unicode_ci ASC LIMIT ?, ?");
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $actors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $countStmt = $mysqli->prepare("SELECT COUNT(id) AS total_count FROM actors");
        $countStmt->execute();
        $totalActors = $countStmt->get_result()->fetch_assoc()['total_count'];

        return ['total_count' => $totalActors, 'data' => $actors];
    }

    public static function getActorById($actorId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT * FROM actors WHERE id = ?");
        $stmt->bind_param("i", $actorId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result;
    }

    public static function updateActor($actorId, $firstName, $lastName)
    {
        $mysqli = dbConnect();
        $updateStmt = $mysqli->prepare("UPDATE actors SET first_name=?, last_name=? WHERE id=?");
        $updateStmt->bind_param('ssi', $firstName, $lastName, $actorId);
        $updateStmt->execute();
    }

    public static function deleteActor($actorId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("DELETE FROM actors WHERE id=?");
        $stmt->bind_param("i", $actorId);
        $stmt->execute();
    }

    public static function searchActors($searchValue, $page = 1, $limit = 10)
    {
        $mysqli = dbConnect();
        $searchValue = '%' . $searchValue . '%';
        $offset = ($page - 1) * $limit;

        $countStmt = $mysqli->prepare("SELECT COUNT(id) AS total_count FROM actors WHERE first_name LIKE ? OR last_name LIKE ?");
        $countStmt->bind_param("ss", $searchValue, $searchValue);
        $countStmt->execute();
        $totalRows = $countStmt->get_result()->fetch_assoc()['total_count'];

        $stmt = $mysqli->prepare("SELECT * FROM actors WHERE first_name LIKE ? OR last_name LIKE ? LIMIT ?, ?");
        $stmt->bind_param("ssii", $searchValue, $searchValue, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['total_count' => $totalRows, 'data' => $result];
    }
}
