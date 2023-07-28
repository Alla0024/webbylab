<?php
require_once("config/db.php");
require_once("models/movie.php");
require_once("models/actor.php");

function parseMovieData($movieData)
{
    $movies = explode("\n\n", $movieData);
    $parsedMovies = [];

    foreach ($movies as $movie) {
        $movieObj = [];
        $lines = explode("\n", $movie);

        foreach ($lines as $line) {
            $parts = explode(": ", $line, 2);
            if (count($parts) === 2) {
                list($key, $value) = $parts;
                $movieObj[strtolower(str_replace(" ", "_", trim($key)))] = trim($value);
            }
        }

        $parsedMovies[] = $movieObj;
    }

    return $parsedMovies;
}
function checkValidYear($year)
{
    $min_year = 1850;
    $max_year = 2023;

    if (is_numeric($year) && $year >= $min_year && $year <= $max_year) {
        return true;
    } else {
        return false;
    }
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'default';

switch ($action) {
    case "delete_movie":
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_id"])) {
            $movieId = $_POST["movie_id"];
            Movie::deleteMovie($movieId);

            echo "Movie deleted successfully";
        } else {
            echo "Invalid request for delete_movie action.";
        }
        break;

    case "get_movie_data":
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['movie_id'])) {
            $movie_id = $_GET['movie_id'];

            $movie = Movie::getMovieById($movie_id);

            if ($movie) {
                header('Content-Type: application/json');
                echo json_encode($movie);
            } else {
                header('HTTP/1.1 404 Not Found');
                echo "Movie not found";
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo "Invalid request for get_movie_data action.";
        }
        break;

    case "import_movies":
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_data"])) {
            $movieData = $_POST["movie_data"];
            $moviesToInsert = parseMovieData($movieData);

            foreach ($moviesToInsert as $movieObj) {
                if (isset($movieObj["title"]) && isset($movieObj["stars"]) && isset($movieObj["release_year"]) && isset($movieObj["format"])) {
                    $actorNames = explode(", ", $movieObj["stars"]);

                    $movieId = Movie::insertMovie($movieObj["title"], $movieObj["release_year"], $movieObj["format"]);
                    if ($movieId) {
                        foreach ($actorNames as $actorName) {
                            $actorFullNameParts = explode(" ", $actorName);
                            $actorFirstName = $actorFullNameParts[0];
                            $actorLastName = count($actorFullNameParts) > 1 ? $actorFullNameParts[1] : "";

                            $actorId = Actor::getOrCreateActor($actorFirstName, $actorLastName);

                            Actor::insertActorsMovies($actorId, $movieId);
                        }
                        echo $movieObj["title"].  " imported successfully!<br>";
                    }else {
                        echo $movieObj["title"] . " already exists!<br>";
                    }
                }

            }
        } else {
            echo "Invalid request for import_movies action.";
        }
        break;

    case "search_actors":
        if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['searchValue'])) {
            $searchValue = $_POST['searchValue'];

            $results = Movie::searchMoviesAndActorsByTitleOrActor($searchValue);
            $totalMovies = count($results);
            $response = array(
                'movies' => $results,
                'totalMovies' => $totalMovies
            );
            echo json_encode($response);
        } else {
            echo "Invalid request for search_actors action.";
        }
        break;

    case "update_movie":
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $movieId = $_POST['movie_id'] ?: '';
            $movieTitle = trim($_POST['movie_title']) ?: '';
            $releaseYear = trim($_POST['release_year']) ?: '';
            $format = $_POST['format'] ?: '';
            $actorId = $_POST['actor_ids'] ?: '';

            if (empty($movieTitle) || empty($releaseYear) || empty($format) || empty($actorId)) {
                http_response_code(400);
                echo "Error: Please fill in all required fields.";
                exit;
            }


            $valid_year = checkValidYear($releaseYear);
            if (!$valid_year) {
                http_response_code(400);
                echo "Error: Invalid release year. Please enter a year between 1850 and 2023.";
                exit;
            }
            if (Movie:: checkMovieExist($movieTitle, $releaseYear, $format)){
                http_response_code(400);
                echo "Movie already exists";
                exit;
            }

            if (empty($movieId)) {
                Movie::addMovie($movieTitle, $releaseYear, $format, $actorId);
                echo "Movie added successfully";
            } else {
                Movie::updateMovie($movieId, $movieTitle, $releaseYear, $format, $actorId);
                echo "Movie updated successfully";

            }
        } else {
            echo "Invalid request for update_movie action.";
        }
        break;

    default:
        echo "Invalid action.";
        break;
}
?>
