<?php

class Movie
{
    function insertMovie($title, $releaseYear, $format)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("INSERT INTO movies (title, release_year, format) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $title, $releaseYear, $format);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public static function addMovie($movieTitle, $releaseYear, $format, $actorId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("INSERT INTO movies (title, release_year, format) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $movieTitle, $releaseYear, $format);
        $stmt->execute();

        $movieId = $stmt->insert_id;

        $stmt = $mysqli->prepare("INSERT INTO actor_movie (actor_id, movie_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $actorId, $movieId);
        $stmt->execute();
    }

    public static function getAllMovies()
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT m.*, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS actor_name 
                                        FROM movies m 
                                        LEFT JOIN actor_movie am ON m.id = am.movie_id 
                                        LEFT JOIN actors a ON am.actor_id = a.id 
                                        GROUP BY m.id
                                        ORDER BY m.title ASC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getMovieById($movieId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("SELECT m.*, 
                                        (SELECT GROUP_CONCAT(am.actor_id) 
                                         FROM actor_movie am 
                                         WHERE am.movie_id = m.id) AS actor_ids,
                                        (SELECT GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name)) 
                                         FROM actor_movie am 
                                         LEFT JOIN actors a ON am.actor_id = a.id 
                                         WHERE am.movie_id = m.id) AS actor_name
                                         FROM movies m
                                         WHERE m.id = ?");
        $stmt->bind_param("i", $movieId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $result['actor_ids'] = explode(',', $result['actor_ids']);

        return $result;
    }


    public static function updateMovie($movieId, $title, $releaseYear, $format, $actorIds)
    {
        $mysqli = dbConnect();

        $deleteStmt = $mysqli->prepare("DELETE FROM actor_movie WHERE movie_id = ?");
        $deleteStmt->bind_param("i", $movieId);
        $deleteStmt->execute();

        $insertStmt = $mysqli->prepare("INSERT INTO actor_movie (actor_id, movie_id) VALUES (?, ?)");
        foreach ($actorIds as $actorId) {
            $insertStmt->bind_param("ii", $actorId, $movieId);
            $insertStmt->execute();
        }

        $updateStmt = $mysqli->prepare("UPDATE movies SET title=?, release_year=?, format=? WHERE id=?");
        $updateStmt->bind_param('sisi', $title, $releaseYear, $format, $movieId);
        $updateStmt->execute();
    }

    public static function deleteMovie($movieId)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("DELETE FROM movies WHERE id=?");
        $stmt->bind_param("i", $movieId);
        $stmt->execute();
    }

    public function searchMoviesAndActorsByTitleOrActor($searchValue)
    {
        $mysqli = dbConnect();
        $searchValue = '%' . $searchValue . '%';
        $stmt = $mysqli->prepare("SELECT m.id, 
                                               m.title, 
                                               m.release_year, 
                                               m.format, 
                                               GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS actor_names
                                        FROM movies m
                                        INNER JOIN actor_movie am ON m.id = am.movie_id
                                        INNER JOIN actors a ON am.actor_id = a.id
                                        WHERE m.id LIKE ? 
                                           OR m.title LIKE ? 
                                           OR CONCAT(a.first_name, ' ', a.last_name) LIKE ? 
                                           OR m.release_year LIKE ? 
                                           OR m.format LIKE ?
                                        GROUP BY m.id, m.title, m.release_year, m.format");
        $stmt->bind_param("sssss", $searchValue, $searchValue, $searchValue, $searchValue, $searchValue);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}