<?php

class Movie
{
    public static function checkMovieExist($title, $releaseYear, $format, $excludeMovieId = null)
    {
        $mysqli = dbConnect();

        $exist = false;
        $sql = "SELECT id FROM movies WHERE title = ? AND release_year = ? AND format = ?";

        if ($excludeMovieId !== null) {
            $sql .= " AND id != ?";
        }

        $stmt_check = $mysqli->prepare($sql);

        if ($excludeMovieId !== null) {
            $stmt_check->bind_param("sisi", $title, $releaseYear, $format, $excludeMovieId);
        } else {
            $stmt_check->bind_param("sis", $title, $releaseYear, $format);
        }

        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $exist = true;
        }
        return $exist;
    }
    public static function insertMovie($title, $releaseYear, $format)
    {
        $mysqli = dbConnect();
        if (self::checkMovieExist($title, $releaseYear, $format)) {
            return false;
        }
        $stmt_check = $mysqli->prepare("SELECT id FROM movies WHERE title = ? AND release_year = ? AND format = ?");
        $stmt_check->bind_param("sis", $title, $releaseYear, $format);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            return false;
        }

        $stmt_insert = $mysqli->prepare("INSERT INTO movies (title, release_year, format) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sis", $title, $releaseYear, $format);
        $stmt_insert->execute();

        return $stmt_insert->insert_id;
    }


    public static function addMovie($movieTitle, $releaseYear, $format, $actorIds)
    {
        $mysqli = dbConnect();
        $stmt = $mysqli->prepare("INSERT INTO movies (title, release_year, format) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $movieTitle, $releaseYear, $format);
        $stmt->execute();

        $movieId = $stmt->insert_id;

        $insertStmt = $mysqli->prepare("INSERT INTO actor_movie (actor_id, movie_id) VALUES (?, ?)");
        foreach ($actorIds as $actorId) {
            $insertStmt->bind_param("ii", $actorId, $movieId);
            $insertStmt->execute();
        }
    }

    public static function getAllMovies($page = 1, $limit = 10)
    {
        $mysqli = dbConnect();
        $offset = ($page - 1) * $limit;

        $stmt = $mysqli->prepare("SELECT m.*, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS actor_names 
                                    FROM movies m 
                                    LEFT JOIN actor_movie am ON m.id = am.movie_id 
                                    LEFT JOIN actors a ON am.actor_id = a.id 
                                    GROUP BY m.id
                                    ORDER BY m.title COLLATE utf8mb4_unicode_ci ASC
                                    LIMIT ?, ?");
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $movies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $countStmt = $mysqli->prepare("SELECT COUNT(DISTINCT m.id) AS total_count FROM movies m");
        $countStmt->execute();
        $totalMovies = $countStmt->get_result()->fetch_assoc()['total_count'];

        return ['total_count' => $totalMovies, 'data' => $movies];
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
        if ($result) {
            if ($result['actor_ids'] !== NULL) {
                $result['actor_ids'] = explode(',', $result['actor_ids']);
            } else {
                $result['actor_ids'] = [];
            }
        }
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

    public static function searchMovies($searchValue, $page = 1, $limit = 10)
    {
        $mysqli = dbConnect();
        $searchValue = '%' . $searchValue . '%';
        $offset = ($page - 1) * $limit;

        $countStmt = $mysqli->prepare("SELECT COUNT(DISTINCT m.id) AS total_count
                                   FROM movies m
                                   INNER JOIN actor_movie am ON m.id = am.movie_id
                                   INNER JOIN actors a ON am.actor_id = a.id
                                   WHERE m.id LIKE ? 
                                      OR m.title LIKE ? 
                                      OR CONCAT(a.first_name, ' ', a.last_name) LIKE ? 
                                      OR m.release_year LIKE ? 
                                      OR m.format LIKE ?");
        $countStmt->bind_param("sssss", $searchValue, $searchValue, $searchValue, $searchValue, $searchValue);
        $countStmt->execute();
        $totalRows = $countStmt->get_result()->fetch_assoc()['total_count'];

        $stmt = $mysqli->prepare("SELECT m.*,
                                       GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS actor_names
                                FROM movies m
                                INNER JOIN actor_movie am ON m.id = am.movie_id
                                INNER JOIN actors a ON am.actor_id = a.id
                                WHERE m.id LIKE ? 
                                   OR m.title LIKE ? 
                                   OR CONCAT(a.first_name, ' ', a.last_name) LIKE ? 
                                   OR m.release_year LIKE ? 
                                   OR m.format LIKE ?
                                GROUP BY m.id, m.title, m.release_year, m.format
                                 LIMIT ?, ?");
        $stmt->bind_param("sssssii", $searchValue, $searchValue, $searchValue, $searchValue, $searchValue, $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['total_count' => $totalRows, 'data' => $result];
    }
    public static function exportMoviesToTxt()
    {
        $mysqli = dbConnect();

        $stmt = $mysqli->prepare("SELECT m.title, m.release_year, m.format, GROUP_CONCAT(CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS stars 
                  FROM movies m 
                  LEFT JOIN actor_movie am ON m.id = am.movie_id 
                  LEFT JOIN actors a ON am.actor_id = a.id 
                  GROUP BY m.id");

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            return false;
        }

        $txtContent = "";
        while ($row = $result->fetch_assoc()) {
            $txtContent .= "Title: {$row['title']}\n";
            $txtContent .= "Release Year: {$row['release_year']}\n";
            $txtContent .= "Format: {$row['format']}\n";
            $txtContent .= "Stars: {$row['stars']}\n\n";
        }

        return $txtContent;
    }
}