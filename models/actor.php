<?php

class Actor
{

    public static function getAllActors()
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT * FROM actors");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        $stmt = $mysqli->prepare("INSERT INTO actors (first_name, last_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $firstName, $lastName);
        $stmt->execute();
    }

    public static function getActorById($actorId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT * FROM actors WHERE id = ?");
        $stmt->bind_param("i", $actorId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function updateActor($actorId, $firstName, $lastName)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("UPDATE actors SET first_name=?, last_name=? WHERE id=?");
        $stmt->bind_param("ssi", $firstName, $lastName, $actorId);
        $stmt->execute();
    }

    public static function deleteActor($actorId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("DELETE FROM actors WHERE id=?");
        $stmt->bind_param("i", $actorId);
        $stmt->execute();
    }

    public static function searchActorsByName($searchValue)
    {
        $mysqli = dbConnect();
        $searchValue = '%' . $searchValue . '%';
        $stmt = $mysqli->prepare("SELECT * FROM actors WHERE id LIKE ? OR first_name LIKE ? OR last_name LIKE ?");
        $stmt->bind_param("sss", $searchValue, $searchValue, $searchValue);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}







